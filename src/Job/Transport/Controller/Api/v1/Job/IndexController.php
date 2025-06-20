<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Job;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\JobRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Job
 */
#[AsController]
#[OA\Tag(name: 'Job')]
readonly class IndexController
{
    public function __construct(
        private SerializerInterface $serializer,
        private JobRepository       $jobRepository,
        private UserProxy           $userProxy
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/job',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $users = $this->userProxy->getUsers();
        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user['id']] = $user;
        }

        $qb = $this->jobRepository->createQueryBuilder('j');

        // Filtres simples
        if ($title = $request->query->get('title')) {
            $qb->andWhere('j.title LIKE :title')
                ->setParameter('title', "%$title%");
        }

        if ($companyName = $request->query->get('company')) {
            $qb->join('j.company', 'c')
                ->andWhere('c.name LIKE :companyName')
                ->setParameter('companyName', "%$companyName%");
        }

        if ($location = $request->query->get('location')) {
            $qb->join('j.company', 'c2') // alias distinct
            ->andWhere('c2.location LIKE :location')
                ->setParameter('location', "%$location%");
        }

        if ($experience = $request->query->get('experience')) {
            $qb->andWhere('j.experience = :experience')
                ->setParameter('experience', $experience);
        }

        if ($contractType = $request->query->get('contractType')) {
            $qb->andWhere('j.contractType = :contractType')
                ->setParameter('contractType', $contractType);
        }

        if ($workType = $request->query->get('workType')) {
            $qb->andWhere('j.workType = :workType')
                ->setParameter('workType', $workType);
        }

        // Tri par date (nouveaux d'abord)
        $qb->orderBy('j.createdAt', 'DESC');

        // Pagination
        $page = max((int)$request->query->get('page', 1), 1);
        $limit = max((int)$request->query->get('limit', 10), 1);
        $offset = ($page - 1) * $limit;

        $qb->setFirstResult($offset)->setMaxResults($limit);

        $jobs = $qb->getQuery()->getResult();

        $response = [];
        foreach ($jobs as $key => $job) {
            $response[$key] = $job->toArray();
            $response[$key]['user'] = $usersById[$job->getUser()->toString()] ?? null;
        }

        $output = JSON::decode(
            $this->serializer->serialize(
                $response,
                'json',
                [
                    'groups' => 'Job',
                ]
            ),
            true,
        );

        return new JsonResponse([
            'data' => $output,
            'page' => $page,
            'limit' => $limit,
            'count' => count($output),
        ]);
    }
}
