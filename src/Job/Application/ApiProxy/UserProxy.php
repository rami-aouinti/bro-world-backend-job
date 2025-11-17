<?php

declare(strict_types=1);

namespace App\Job\Application\ApiProxy;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class UserProxy
 *
 * @package App\Blog\Application\ApiProxy
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class UserProxy
{

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $broWorldApiBaseUrl,
        private string $broWorldApiToken,
        private string $mediaApiBaseUrl,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getUsers(): array
    {
        $response = $this->httpClient->request('GET', $this->buildUrl($this->broWorldApiBaseUrl, '/api/v1/user'), [
            'headers' => [
                'Authorization' => sprintf('ApiKey %s', $this->broWorldApiToken),
            ],
        ]);

        return $response->toArray();
    }

    /**
     * @param $mediaId
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @return array
     */
    public function getMedia($mediaId): array
    {
        $response = $this->httpClient->request(
            'GET',
            $this->buildUrl($this->mediaApiBaseUrl, sprintf('v1/platform/media/%s', $mediaId))
        );

        return $response->toArray();
    }

    private function buildUrl(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}
