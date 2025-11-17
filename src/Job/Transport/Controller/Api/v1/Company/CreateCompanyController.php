<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Company;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\Service\CompanyService;
use App\Job\Domain\Entity\Company;
use App\Job\Infrastructure\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $company = new Company();
        $company->setName($request->request->get('name'));
        $company->setDescription($request->request->get('description'));
        $company->setLocation($request->request->get('location') ?? '');
        $company->setContactEmail($request->request->get('contactEmail') ?? '');
        if ($request->files->get('file')) {
            try {
                $logo = $this->companyService->uploadLogo($request);
            } catch (FileException $exception) {
                return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
            }

            $company->setLogo($logo);
        }

        $company->setSiteUrl($request->request->get('siteUrl') ?? '');
        //$company->setMedias($jsonParams['medias'] ?? []);
        $company->setUser(Uuid::fromString($loggedInUser->getId()));
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
