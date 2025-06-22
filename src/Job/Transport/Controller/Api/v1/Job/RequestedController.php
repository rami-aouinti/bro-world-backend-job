<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Job;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\ApplicantRepository;
use App\Job\Infrastructure\Repository\JobApplicationRepository;
use App\Job\Infrastructure\Repository\JobRepository;
use Doctrine\DBAL\Connection;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsController]
#[OA\Tag(name: 'Job')]
readonly class RequestedController
{
    public function __construct(
        private SerializerInterface $serializer,
        private JobRepository       $jobRepository,
        private UserProxy           $userProxy,
        private JobApplicationRepository $jobApplicationRepository,
        private ApplicantRepository $applicantRepository
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    #[Route(path: '/v1/requests/job', methods: [Request::METHOD_GET])]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $page = max((int)$request->query->get('page', 1), 1);
        $limit = max((int)$request->query->get('limit', 20), 1);
        $offset = ($page - 1) * $limit;

        // Sinon, fallback sur DQL classique
        $users = $this->userProxy->getUsers();
        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user['id']] = $user;
        }

        $qb = $this->jobRepository->createQueryBuilder('j');

        if ($title = $request->query->get('title')) {
            $qb->andWhere('j.title LIKE :title')
                ->orWhere('j.description LIKE :title')
                ->setParameter('title', "%$title%");
        }

        if ($companyName = $request->query->get('company')) {
            $qb->join('j.company', 'c')
                ->andWhere('c.name LIKE :companyName')
                ->setParameter('companyName', "%$companyName%");
        }

        if ($location = $request->query->get('location')) {
            $qb->join('j.company', 'c2')
                ->andWhere('j.workLocation LIKE :location')
                ->orWhere('c2.location LIKE :location')
                ->setParameter('location', "%$location%");
        }

        if ($experience = $request->query->get('experience')) {
            $qb->andWhere('j.experience = :experience')
                ->setParameter('experience', $experience);
        }

        $works = $request->query->all('works');
        if (!empty($works)) {
            $qb->andWhere('j.workType IN (:works)')
                ->setParameter('works', $works);
        }

        $contracts = $request->query->all('contracts');
        if (!empty($contracts)) {
            $qb->andWhere('j.contractType IN (:contracts)')
                ->setParameter('contracts', $contracts);
        }

        $skills = $request->query->all('skills');
        if (!empty($skills)) {
            $qb->andWhere('j.requiredSkills IN (:skills)')
                ->setParameter('skills', $skills);
        }

        $qb->orderBy('j.createdAt', 'DESC');
        $qb->setFirstResult($offset)->setMaxResults($limit);

        $jobs = $qb->getQuery()->getResult();

        $applicants = $this->applicantRepository->findBy([
            'user' => $loggedInUser->getUserIdentifier(),
        ]);
        $response = [];
        foreach ($jobs as $key => $job) {
            $applied = null;
            if(!empty($applicants)) {
                foreach ($applicants as $applicant) {
                    $applied = $this->jobApplicationRepository->findOneBy([
                        'applicant' => $applicant->getId(),
                        'job' => $job->getId(),
                    ]);
                }
            }
            if ($applied) {
                $response[$key] = $job->toArray();
                $response[$key]['applied'] = $applied !== null;
                $response[$key]['owner'] = $job->getUser()->toString() === $loggedInUser->getUserIdentifier();
                $response[$key]['user'] = $usersById[$job->getUser()->toString()] ?? null;
            }
        }

        $output = JSON::decode(
            $this->serializer->serialize($response, 'json', [
                'groups' => 'Job',
            ]),
            true
        );
        return new JsonResponse([
            'data' => $output,
            'page' => $page,
            'limit' => $limit,
            'count' => count($this->jobRepository->findAll()),
        ]);
    }
}
