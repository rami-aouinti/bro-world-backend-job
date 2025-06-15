<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Company;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\CompanyRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Company
 */
#[AsController]
#[OA\Tag(name: 'Company')]
readonly class IndexController
{
    public function __construct(
        private SerializerInterface $serializer,
        private CompanyRepository   $companyRepository,
        private UserProxy           $userProxy
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/company',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
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

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $response,
                'json',
                [
                    'groups' => 'Company',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
