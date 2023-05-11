<?php declare(strict_types=1);

namespace Http\Factory;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

interface RequestFactoryInterface extends \Psr\Http\Message\RequestFactoryInterface
{
    /**
     * Create a new request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request. If
     *     the value is a string, the factory MUST create a UriInterface
     *     instance based on it.
     * @param string $protocolVersion
     * @param array $headers
     *
     * @return RequestInterface
     */
    public function createRequest(
        string      $method,
                    $uri,
        string      $protocolVersion = '2',
        array       $headers = []
    ): RequestInterface;
}