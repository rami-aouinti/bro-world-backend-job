<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Applicant;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\Service\ResumeService;
use App\Job\Domain\Entity\Applicant;
use App\Job\Infrastructure\Repository\ApplicantRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Applicant
 */
#[AsController]
#[OA\Tag(name: 'Applicant')]
readonly class CreateApplicantController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ApplicantRepository $applicantRepository,
        private ResumeService       $resumeService
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
        $applicant = new Applicant();
        $applicant->setFirstName($request->request->get('firstName'));
        $applicant->setLastName($request->request->get('lastName'));
        $applicant->setContactEmail($request->request->get('contactEmail'));
        $applicant->setPhone($request->request->get('phone'));
        $applicant->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));
        if ($request->files->get('file')) {
            try {
                $resume = $this->resumeService->uploadCV($request);
            } catch (FileException $exception) {
                return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $applicant->setResume($resume);
        }

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
