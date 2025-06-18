<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Applicant;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Domain\Entity\Applicant;
use App\Job\Infrastructure\Repository\ApplicantRepository;
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
#[OA\Tag(name: 'Applicant')]
readonly class GetApplicantController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ApplicantRepository $applicantRepository,
        private UserProxy           $userProxy
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/applicant/{applicant}',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request, Applicant $applicant): JsonResponse
    {
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
