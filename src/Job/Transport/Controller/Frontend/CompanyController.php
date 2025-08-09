<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Frontend;

use App\General\Domain\Utils\JSON;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\CompanyRepository;
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

/**
 * @package App\Company
 */
#[AsController]
#[OA\Tag(name: 'Company')]
readonly class CompanyController
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
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route(
        path: '/platform/company',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(Request $request): JsonResponse
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
