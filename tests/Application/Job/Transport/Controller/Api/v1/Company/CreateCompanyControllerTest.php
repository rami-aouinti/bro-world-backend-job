<?php

declare(strict_types=1);

namespace App\Tests\Application\Job\Transport\Controller\Api\v1\Company;

use App\General\Domain\Utils\JSON;
use App\Tests\TestCase\WebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

use const UPLOAD_ERR_CANT_WRITE;

/**
 * @package App\Tests
 */
class CreateCompanyControllerTest extends WebTestCase
{
    private string $baseUrl = self::API_URL_PREFIX . '/v1/company';

    #[TestDox('Test that `POST /v1/company` with invalid logo returns clean error response.')]
    public function testThatCreateActionWithInvalidLogoReturnsErrorResponse(): void
    {
        $client = $this->getTestClient('john-admin', 'password-admin');

        $client->request(
            method: 'POST',
            uri: $this->baseUrl,
            parameters: [
                'name' => 'Acme Corporation',
                'description' => 'Company logo upload failure scenario.',
                'location' => '12345 Sample City',
                'contactEmail' => 'contact@example.com',
                'siteUrl' => 'https://example.com',
            ],
            files: [
                'file' => new UploadedFile(
                    __FILE__,
                    'logo.txt',
                    'text/plain',
                    UPLOAD_ERR_CANT_WRITE,
                    true
                ),
            ]
        );

        $response = $client->getResponse();
        $content = $response->getContent();

        self::assertNotFalse($content);
        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), "Response:\n" . $response);

        /** @var array{message: string} $responseData */
        $responseData = JSON::decode($content, true);

        self::assertArrayHasKey('message', $responseData);
        self::assertSame('Failed to upload company logo.', $responseData['message']);
    }
}
