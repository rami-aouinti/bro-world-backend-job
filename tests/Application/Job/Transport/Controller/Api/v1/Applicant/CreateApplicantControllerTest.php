<?php

declare(strict_types=1);

namespace App\Tests\Application\Job\Transport\Controller\Api\v1\Applicant;

use App\General\Domain\Utils\JSON;
use App\Job\Application\Service\ResumeService;
use App\Tests\TestCase\WebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

use function file_put_contents;
use function is_file;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @package App\Tests
 */
class CreateApplicantControllerTest extends WebTestCase
{
    private string $baseUrl = self::API_URL_PREFIX . '/v1/applicant';

    /**
     * @throws Throwable
     */
    #[TestDox('POST /api/v1/applicant returns an error response when CV upload fails.')]
    public function testThatUploadFailureReturnsApiErrorResponse(): void
    {
        $client = $this->getTestClient('john-user', 'password-user');

        $resumeService = $this->createMock(ResumeService::class);
        $resumeService
            ->method('uploadCV')
            ->willThrowException(new FileException('Unable to move file.'));

        static::getContainer()->set(ResumeService::class, $resumeService);

        $tempFile = tempnam(sys_get_temp_dir(), 'cv');
        self::assertIsString($tempFile);
        file_put_contents($tempFile, 'dummy');

        $uploadedFile = new UploadedFile(
            $tempFile,
            'resume.pdf',
            'application/pdf',
            null,
            true
        );

        try {
            $client->request(
                'POST',
                $this->baseUrl,
                [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'contactEmail' => 'john@example.com',
                    'phone' => '1234567890',
                ],
                [
                    'file' => $uploadedFile,
                ],
                [
                    'CONTENT_TYPE' => 'multipart/form-data',
                ]
            );

            $response = $client->getResponse();
            $content = $response->getContent();
            self::assertNotFalse($content);
            self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode(), "Response:\n" . $response);

            $responseData = JSON::decode($content, true);
            self::assertIsArray($responseData);
            self::assertArrayHasKey('error', $responseData);
            self::assertSame('Unable to move file.', $responseData['error']);
        } finally {
            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
