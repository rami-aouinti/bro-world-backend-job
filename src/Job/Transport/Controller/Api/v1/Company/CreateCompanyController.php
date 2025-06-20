<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Company;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\Service\CompanyService;
use App\Job\Domain\Entity\Company;
use App\Job\Domain\Entity\Job;
use App\Job\Infrastructure\Repository\CompanyRepository;
use App\Job\Infrastructure\Repository\JobRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @package App\Job
 */
#[AsController]
#[OA\Tag(name: 'Company')]
readonly class CreateCompanyController
{
    public function __construct(
        private SerializerInterface $serializer,
        private CompanyRepository   $companyRepository,
        private ValidatorInterface  $validator,
        private CompanyService      $companyService
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/company',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $jsonParams = $request->request->all();

        $company = new Company();
        $company->setName($jsonParams['name']);
        $company->setDescription($jsonParams['description']);
        $company->setLocation($jsonParams['location']);
        $company->setContactEmail($jsonParams['contactEmail']);
        if($request->files->get('file')) {
            $logo = $this->companyService->uploadLogo($request);
            $company->setLogo($logo);
        }

        $company->setSiteUrl($jsonParams['siteUrl'] ?? '');
        //$company->setMedias($jsonParams['medias'] ?? []);
        $company->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));
        $violations = $this->validator->validate($company);
        $this->companyRepository->save($company, true);
        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $company,
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
