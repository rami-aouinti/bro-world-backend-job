<?php

declare(strict_types=1);

namespace App\Tests\Integration\Job\Transport\Controller\Frontend;

use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Domain\Entity\Company;
use App\Job\Domain\Entity\Job;
use App\Job\Domain\Enum\ContractType;
use App\Job\Domain\Enum\WorkType;
use App\Tests\TestCase\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function array_column;
use function array_values;
use function json_decode;
use function sprintf;
use function strtolower;
use function str_replace;

use const JSON_THROW_ON_ERROR;

/**
 * @package App\Tests\Integration\Job\Transport\Controller\Frontend
 */
final class JobFrontendControllerTest extends WebTestCase
{
    /**
     * @throws JsonException
     */
    public function testFilterJobsBySingleSkill(): void
    {
        $client = $this->getTestClient();

        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $cache = $container->get(TagAwareCacheInterface::class);
        $cache->clear();

        $users = $this->createJobs($entityManager);

        $container->set(UserProxy::class, $this->createUserProxyMock($users));

        $client->request('GET', '/platform/job', ['skills' => ['php']]);

        $response = $client->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        /** @var array{data: array<int, array<string, mixed>>} $payload */
        $payload = json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $titles = array_values(array_column($payload['data'], 'title'));

        static::assertCount(2, $titles);
        static::assertContains('PHP and Symfony Developer', $titles);
        static::assertContains('PHP Developer', $titles);
    }

    /**
     * @throws JsonException
     */
    public function testFilterJobsByMultipleSkills(): void
    {
        $client = $this->getTestClient();

        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $cache = $container->get(TagAwareCacheInterface::class);
        $cache->clear();

        $users = $this->createJobs($entityManager);

        $container->set(UserProxy::class, $this->createUserProxyMock($users));

        $client->request('GET', '/platform/job', ['skills' => ['php', 'symfony']]);

        $response = $client->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        /** @var array{data: array<int, array<string, mixed>>} $payload */
        $payload = json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $titles = array_values(array_column($payload['data'], 'title'));

        static::assertSame(['PHP and Symfony Developer'], $titles);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function createJobs(EntityManagerInterface $entityManager): array
    {
        $users = [];

        $this->persistJob($entityManager, 'PHP and Symfony Developer', ['php', 'symfony'], $users);
        $this->persistJob($entityManager, 'Go Developer', ['go'], $users);
        $this->persistJob($entityManager, 'PHP Developer', ['php'], $users);

        $entityManager->flush();

        return $users;
    }

    /**
     * @param array<int, array<string, string>> $users
     */
    private function persistJob(
        EntityManagerInterface $entityManager,
        string $title,
        array $skills,
        array &$users
    ): void {
        $companyUser = Uuid::uuid4();
        $company = new Company();
        $company->setName($title . ' Company');
        $company->setDescription($title . ' description');
        $company->setLocation('Remote');
        $company->setContactEmail(sprintf('%s@company.example', strtolower(str_replace(' ', '.', $title))));
        $company->setUser($companyUser);

        $entityManager->persist($company);

        $jobUser = Uuid::uuid4();
        $job = new Job();
        $job->setTitle($title);
        $job->setDescription($title . ' description');
        $job->setWork($title . ' work');
        $job->setRequiredSkills($skills);
        $job->setExperience('mid');
        $job->setWorkType(WorkType::REMOTE);
        $job->setContractType(ContractType::FULLTIME);
        $job->setCompany($company);
        $job->setUser($jobUser);

        $entityManager->persist($job);

        $users[] = [
            'id' => $jobUser->toString(),
            'email' => sprintf('%s@example.com', strtolower(str_replace(' ', '.', $title))),
        ];
    }

    /**
     * @param array<int, array<string, string>> $users
     *
     * @return UserProxy&MockObject
     */
    private function createUserProxyMock(array $users): UserProxy
    {
        $userProxy = $this->createMock(UserProxy::class);
        $userProxy->method('getUsers')->willReturn($users);

        return $userProxy;
    }
}

