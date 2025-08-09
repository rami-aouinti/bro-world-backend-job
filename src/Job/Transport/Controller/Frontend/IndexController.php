<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Frontend;

use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\JobRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsController]
#[OA\Tag(name: 'Job')]
readonly class IndexController
{
    public function __construct(
        private JobRepository       $jobRepository,
        private UserProxy           $userProxy,
        private TagAwareCacheInterface $cache
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    #[Route(path: '/platform/job', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request): JsonResponse
    {
        $page = max((int)$request->query->get('page', 1), 1);
        $limit = max((int)$request->query->get('limit', 20), 1);
        $offset = ($page - 1) * $limit;
        $cacheKey = "public_jobs_page_{$page}_limit_{$limit}";

        $result = $this->cache->get($cacheKey, function (ItemInterface $item) use ($limit, $offset, $page, $request) {
            $item->tag(['jobs', 'public_jobs']);
            $item->expiresAfter(20);
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

            $data = [];

            foreach ($jobs as $key => $job) {
                $applied = null;
                $data[$key] = $job->toArray();
                $data[$key]['owner'] = null;
                $data[$key]['applied'] = $applied !== null;
                $data[$key]['user'] = $usersById[$job->getUser()->toString()] ?? null;
            }
            return ['data' => $data, 'page' => $page, 'limit' => $limit, 'count' => count($this->jobRepository->findAll())];
        });

        return new JsonResponse($result);
    }
}
