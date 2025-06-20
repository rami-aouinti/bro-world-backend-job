<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Job;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\JobRepository;
use Doctrine\DBAL\Connection;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[OA\Tag(name: 'Job')]
readonly class IndexController
{
    public function __construct(
        private SerializerInterface $serializer,
        private JobRepository       $jobRepository,
        private UserProxy           $userProxy,
        private Connection          $connection, // injecté automatiquement
    ) {
    }

    #[Route(path: '/v1/job', methods: [Request::METHOD_GET])]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $skills = $request->query->all('skills');
        $page = max((int)$request->query->get('page', 1), 1);
        $limit = max((int)$request->query->get('limit', 10), 1);
        $offset = ($page - 1) * $limit;

        if (!empty($skills)) {
            $sql = 'SELECT * FROM job WHERE 1=1';
            $parameters = [];

            foreach ($skills as $index => $skill) {
                $sql .= " AND JSON_CONTAINS(required_skills, :skill$index)";
                $parameters["skill$index"] = json_encode([$skill]);
            }

            $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
            $parameters['limit'] = $limit;
            $parameters['offset'] = $offset;

            $stmt = $this->connection->prepare($sql);
            $results = $stmt->executeQuery($parameters)->fetchAllAssociative();

            return new JsonResponse([
                'data' => $results,
                'page' => $page,
                'limit' => $limit,
                'count' => count($results),
            ]);
        }

        // Sinon, fallback sur DQL classique
        $users = $this->userProxy->getUsers();
        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user['id']] = $user;
        }

        $qb = $this->jobRepository->createQueryBuilder('j');

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
            $qb->join('j.company', 'c2')
                ->andWhere('c2.location LIKE :location')
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

        $qb->orderBy('j.createdAt', 'DESC');
        $qb->setFirstResult($offset)->setMaxResults($limit);

        $jobs = $qb->getQuery()->getResult();

        $response = [];
        foreach ($jobs as $key => $job) {
            $response[$key] = $job->toArray();
            $response[$key]['user'] = $usersById[$job->getUser()->toString()] ?? null;
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
            'count' => count($output),
        ]);
    }
}
