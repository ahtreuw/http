<?php declare(strict_types=1);

namespace Http\Factory;

use Http\HTTP;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Http\Message\ServerRequest;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    #[Pure] public function __construct(
        private readonly UriFactoryInterface    $uriFactory = new UriFactory,
        private readonly StreamFactoryInterface $streamFactory = new StreamFactory,
        private readonly UploadedFileFactoryInterface $uploadedFileFactory = new UploadedFileFactory
    )
    {
    }

    public function createServerRequest(
        string $method,
               $uri,
        array  $serverParams = [],
        array  $attributes = []
    ): ServerRequestInterface
    {
        $uri = $uri instanceof UriInterface ? $uri : $this->uriFactory->createUri($uri);

        parse_str($uri->getQuery(), $queryParams);

        return new ServerRequest(
            method: $method,
            uri: $uri,
            body: $this->createBody(),
            protocolVersion: $this->getProtocolVersion($serverParams['SERVER_PROTOCOL'] ?? null),
            headers: $this->getAllHeaders($serverParams),
            attributes: $attributes,
            cookieParams: $_COOKIE ?? [],
            parsedBody: $this->getParsedBody(),
            queryParams: $queryParams,
            serverParams: $serverParams,
            uploadedFiles: $this->normalizeFiles($_FILES ?? []),
        );
    }

    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        $serverParams = $_SERVER ?? [];

        $method = php_sapi_name() === 'cli' ? 'CLI' : $serverParams['REQUEST_METHOD'] ?? HTTP::METHOD_GET;

        $uri = $this->uriFactory->createUriFromGlobals();

        return $this->createServerRequest($method, $uri, $serverParams);
    }

    private function createBody(): StreamInterface
    {
        return $this->streamFactory->createRequestBodyStream();
    }

    private function getProtocolVersion(null|string $serverProtocol): string
    {
        return $serverProtocol ? str_replace('HTTP/', '', $serverProtocol) : '1.1';
    }

    public function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
                continue;
            }
            if (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = $this->createUploadedFileFromSpec($value);
                continue;
            }
            if (is_array($value)) {
                $normalized[$key] = $this->normalizeFiles($value);
                continue;
            }
            throw new InvalidArgumentException('Invalid value in files specification');
        }
        return $normalized;
    }

    private function createUploadedFileFromSpec(array $value): array|UploadedFileInterface
    {
        if (is_array($value['tmp_name'])) {
            return $this->normalizeNestedFileSpec($value);
        }

        $stream = $this->streamFactory->createStreamFromFile($value['tmp_name']);

        return $this->uploadedFileFactory->createUploadedFile(
            stream: $stream,
            size: intval($value['size']),
            error: intval($value['error']),
            clientFilename: $value['name'],
            clientMediaType: $value['type'],
        );
    }

    private function normalizeNestedFileSpec(array $files = []): array
    {
        $normalizedFiles = [];
        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key] ?? null,
                'error' => $files['error'][$key] ?? null,
                'name' => $files['name'][$key] ?? null,
                'type' => $files['type'][$key] ?? null,
            ];
            $normalizedFiles[$key] = $this->createUploadedFileFromSpec($spec);
        }
        return $normalizedFiles;
    }

    private function getAllHeaders(array $serverParams): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = [];
        foreach ($serverParams as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
                continue;
            }
            if (str_starts_with($name, 'CONTENT_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 8)))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    private function getParsedBody(): array
    {
        return $_POST ?? [];
    }
}
