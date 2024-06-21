<?php declare(strict_types=1);

namespace Http\Factory;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\UriInterface;
use Http\Message\Uri;

class UriFactory implements UriFactoryInterface
{

    #[Pure] public function createUri(string $uri = ''): UriInterface
    {
        return new Uri(...parse_url($uri));
    }

    public function createUriFromServerParams(array $serverParams): UriInterface
    {
        return $this->createUri($this->serverParamsToUri(...$serverParams));
    }

    public function createUriFromGlobals(): UriInterface
    {
        $serverParams = $_SERVER ?? [];
        return $this->createUri($this->serverParamsToUri(...$serverParams));
    }

    protected function serverParamsToUri(
        null|string $REQUEST_SCHEME = null,
        null|string $HTTPS = null,
        null|string $HTTP_HOST = null,
        null|string $SERVER_NAME = null,
        null|string $SERVER_ADDR = null,
        null|string $SERVER_PORT = null,
        null|string $REQUEST_URI = null,
        null|string $QUERY_STRING = null,
        null|array  $argv = null,
        mixed       ...$extra
    ): string
    {
        $scheme = $REQUEST_SCHEME ?? ($HTTPS !== 'off' ? 'https' : 'http');

        if (false === ($host = parse_url($scheme . '://' . $HTTP_HOST, PHP_URL_HOST))) {
            $host = $SERVER_NAME ?? $SERVER_ADDR ?? 'localhost';
        }

        if (false === ($port = parse_url($scheme . '://' . $HTTP_HOST, PHP_URL_PORT))) {
            $port = $SERVER_PORT ?? null;
        }

        $path = trim(preg_replace('/\/+/', '/',
            $REQUEST_URI ?? implode('/', array_slice($argv ?? [], 1))), '/');

        $query = !str_contains($path, '?') && $QUERY_STRING ? '?' . $QUERY_STRING : '';

        return $scheme . '://' . $host . ':' . $port . '/' . $path . $query;
    }
}
