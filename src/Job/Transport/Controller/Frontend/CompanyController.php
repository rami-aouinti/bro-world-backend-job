<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Frontend;

use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\CompanyRepository;
use OpenApi\Attributes as OA;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @package App\Company
 */
#[AsController]
#[OA\Tag(name: 'Company')]
readonly class CompanyController
{
    public function __construct(
        private CompanyRepository   $companyRepository,
        private UserProxy           $userProxy,
        private TagAwareCacheInterface $cache
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route(
        path: '/platform/company',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $cacheKey = "public_companies";

        $result = $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->tag(['companies', 'public_companies']);
            $item->expiresAfter(20);
            $users = $this->userProxy->getUsers();

            $usersById = [];
            foreach ($users as $user) {
                $usersById[$user['id']] = $user;
            }

            $companies = $this->companyRepository->findAll();

            $response = [];
            foreach ($companies as $key => $company){
                $response[$key] = $company->toArray();
                $response[$key]['user'] = $usersById[$company->getUser()->toString()] ?? null;
            }

            return $response;
        });

        return new JsonResponse($result);
    }
}
