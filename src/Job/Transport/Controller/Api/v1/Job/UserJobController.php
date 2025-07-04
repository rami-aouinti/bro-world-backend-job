<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Job;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\JobRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Job
 */
#[AsController]
#[OA\Tag(name: 'Job')]
readonly class UserJobController
{
    public function __construct(
        private SerializerInterface $serializer,
        private JobRepository       $jobRepository,
        private UserProxy           $userProxy
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/profile/job',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $qb = $this->jobRepository->createQueryBuilder('j');
        $qb->andWhere('j.user = :user')
            ->setParameter('user', $loggedInUser->getUserIdentifier());
        $title = $request->query->get('title');
        if ($title !== null) {
            $qb->andWhere('j.title = :title')
                ->setParameter('title', $title);
        }

        $companyName = $request->query->get('company');
        if ($companyName !== null) {
            $qb->join('j.company', 'c')
                ->andWhere('c.name = :companyName')
                ->setParameter('companyName', $companyName);
        }

        $location = $request->query->get('location');
        if ($location !== null) {
            $qb->join('j.company', 'c')
                ->andWhere('c.location = :location')
                ->setParameter('location', $location);
        }

        $jobs = $qb->getQuery()->getResult();

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $jobs,
                'json',
                [
                    'groups' => 'Job',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
