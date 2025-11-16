<?php

namespace App\Job\Transport\Controller\Api\v1;

use App\Job\Application\Messenger\Command\CreateJobCommand;
use App\Job\Application\Messenger\Command\DeleteJobCommand;
use App\Job\Application\Messenger\Command\UpdateJobCommand;
use App\Job\Application\Service\JobCacheKeyGenerator;
use App\Job\Domain\Entity\Job;
use App\Job\Infrastructure\Repository\JobRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[OA\Tag(name: 'Job')]
class JobResourceController extends AbstractController
{
    private const CACHE_TTL = 300;

    /**
     * @throws JsonException
     */
    #[Route(path: '/api/v1/jobs', methods: 'POST')]
    #[OA\Post(description: 'Create job.')]
    #[OA\RequestBody(
        description: 'Json to create the job',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Job title'),
                new OA\Property(property: 'description', type: 'string', example: 'Description of the job'),
                new OA\Property(property: 'requiredSkills', type: 'string', example: 'Skills for the job'),
                new OA\Property(property: 'experience', type: 'string', example: 'Junior'),
                new OA\Property(property: 'companyId', type: 'string', example: '018733fb-d3d2-733a-9184-4a79ab743bd2')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the ID of the job',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'statusCode', type: 'int', example: 201),
                new OA\Property(property: 'message', type: 'string', example: 'Job created'),
                new OA\Property(property: 'data', type: 'object')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'statusCode', type: 'int', example: 400),
                new OA\Property(property: 'message', type: 'string', example: 'Invalid arguments'),
                new OA\Property(property: 'data', type: 'object')
            ]
        )
    )]
    public function createJob(Request $request, MessageBusInterface $commandBus): Response
    {
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $command = new CreateJobCommand(
            $payload['title'] ?? '',
            $payload['description'] ?? '',
            $payload['requiredSkills'] ?? '',
            $payload['experience'] ?? '',
            $payload['companyId'] ?? ''
        );

        try {
            /** @var Job $job */
            $job = $this->dispatchCommand($commandBus, $command);
        } catch (ValidationFailedException $exception) {
            return $this->buildJsonResponse(
                'Invalid input',
                $this->formatViolations($exception->getViolations()),
                Response::HTTP_BAD_REQUEST
            );
        } catch (NotFoundHttpException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), [], Response::HTTP_NOT_FOUND);
        }

        return $this->buildJsonResponse('Job created', [
            'id' => (string) $job->getId(),
        ], Response::HTTP_CREATED);
    }

    #[Route(path: '/api/v1/jobs/{id}', methods: 'GET')]
    #[OA\Get(description: 'Return job by ID.')]
    public function getJobById(
        JobRepository $repository,
        TagAwareCacheInterface $cache,
        string $id
    ): Response {
        try {
            $jobData = $cache->get(
                JobCacheKeyGenerator::buildJobItemKey($id),
                function (ItemInterface $item) use ($repository, $id) {
                    $item->expiresAfter(self::CACHE_TTL);
                    $item->tag(JobCacheKeyGenerator::jobItemTag($id));

                    $job = $repository->find($id);
                    if ($job === null) {
                        throw new NotFoundHttpException(sprintf('Job %s not found', $id));
                    }

                    return $job->toArray();
                }
            );
        } catch (NotFoundHttpException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), ['id' => $id], Response::HTTP_NOT_FOUND);
        }

        return $this->buildJsonResponse('Job by ID', $jobData);
    }

    #[Route(path: '/api/v1/jobs', methods: 'GET')]
    #[OA\Get(description: 'Return the jobs depending on the filter.')]
    public function listJobs(
        Request $request,
        JobRepository $repository,
        TagAwareCacheInterface $cache
    ): Response {
        $filters = array_filter([
            'title' => $request->query->get('title'),
            'company' => $request->query->get('company'),
            'location' => $request->query->get('location'),
        ], static fn ($value) => $value !== null && $value !== '');

        $cacheKey = JobCacheKeyGenerator::buildJobListKey($filters);

        $jobs = $cache->get($cacheKey, function (ItemInterface $item) use ($repository, $filters) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag(JobCacheKeyGenerator::JOB_LIST_TAG);

            $qb = $repository->createQueryBuilder('j');
            $shouldJoinCompany = isset($filters['company']) || isset($filters['location']);

            if ($shouldJoinCompany) {
                $qb->join('j.company', 'company');
            }

            if (isset($filters['title'])) {
                $qb->andWhere('j.title = :title')
                    ->setParameter('title', $filters['title']);
            }

            if (isset($filters['company'])) {
                $qb->andWhere('company.name = :companyName')
                    ->setParameter('companyName', $filters['company']);
            }

            if (isset($filters['location'])) {
                $qb->andWhere('company.location = :location')
                    ->setParameter('location', $filters['location']);
            }

            $result = $qb->getQuery()->getResult();

            return array_map(static fn (Job $job) => $job->toArray(), $result);
        });

        return $this->buildJsonResponse('Filtered Jobs', $jobs);
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/api/v1/jobs/{id}', methods: 'PUT')]
    #[OA\Put(description: 'Update the job by ID.')]
    #[OA\RequestBody(
        description: 'Json to update the job',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Job title'),
                new OA\Property(property: 'description', type: 'string', example: 'Description of the job'),
                new OA\Property(property: 'requiredSkills', type: 'string', example: 'Skills for the job'),
                new OA\Property(property: 'experience', type: 'string', example: 'Junior')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the properties of the job',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'statusCode', type: 'int', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Job updated'),
                new OA\Property(property: 'data', type: 'object')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'statusCode', type: 'int', example: 400),
                new OA\Property(property: 'message', type: 'string', example: 'Invalid arguments'),
                new OA\Property(property: 'data', type: 'object')
            ]
        )
    )]
    public function updateJob(
        Request $request,
        MessageBusInterface $commandBus,
        string $id
    ): Response {
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $command = new UpdateJobCommand(
            $id,
            $payload['title'] ?? null,
            $payload['description'] ?? null,
            $payload['requiredSkills'] ?? null,
            $payload['experience'] ?? null,
            $payload['companyId'] ?? null
        );

        try {
            /** @var Job $job */
            $job = $this->dispatchCommand($commandBus, $command);
        } catch (ValidationFailedException $exception) {
            return $this->buildJsonResponse(
                'Invalid input',
                $this->formatViolations($exception->getViolations()),
                Response::HTTP_BAD_REQUEST
            );
        } catch (NotFoundHttpException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), ['id' => $id], Response::HTTP_NOT_FOUND);
        }

        return $this->buildJsonResponse('Job updated', $job->toArray());
    }

    #[Route(path: '/api/v1/jobs/{id}', methods: 'DELETE')]
    #[OA\Delete(description: 'Delete the job by ID')]
    public function deleteJob(MessageBusInterface $commandBus, string $id): Response
    {
        try {
            /** @var Job $job */
            $job = $this->dispatchCommand($commandBus, new DeleteJobCommand($id));
        } catch (NotFoundHttpException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), ['id' => $id], Response::HTTP_NOT_FOUND);
        }

        return $this->buildJsonResponse('Job deleted', $job->toArray());
    }

    private function dispatchCommand(MessageBusInterface $commandBus, object $command): mixed
    {
        $envelope = $commandBus->dispatch($command);
        /** @var HandledStamp|null $handled */
        $handled = $envelope->last(HandledStamp::class);

        return $handled?->getResult();
    }

    private function buildJsonResponse(string $message, array $data, int $statusCode = 200): JsonResponse
    {
        return $this->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errorData = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errorData;
    }
}
