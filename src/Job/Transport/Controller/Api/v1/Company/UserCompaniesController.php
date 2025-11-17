<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Company;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
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
readonly class UserCompaniesController
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
        path: '/v1/profile/company',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {


        $companies = $this->companyRepository->findBy(
            [
                'user' => $loggedInUser->getId(),
            ]
        );

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $companies,
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
