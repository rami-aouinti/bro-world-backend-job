<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Applicant;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Domain\Entity\Applicant;
use App\Job\Domain\Entity\Company;
use App\Job\Domain\Entity\Job;
use App\Job\Infrastructure\Repository\ApplicantRepository;
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
 * @package App\Applicant
 */
#[AsController]
#[OA\Tag(name: 'Applicant')]
class CreateApplicantController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ApplicantRepository $applicantRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/applicant',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $applicant = new Applicant();
        $applicant->setName($jsonParams['name']);
        $applicant->setContactEmail($jsonParams['contactEmail']);
        $applicant->setJobPreferences($jsonParams['jobPreferences']);
        $applicant->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));
        $violations = $this->validator->validate($applicant);
        $this->applicantRepository->save($applicant, true);
        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $applicant,
                'json',
                [
                    'groups' => 'Applicant',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
