<?php declare(strict_types=1);

namespace Http\Factory;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Http\Message\Request;

class RequestFactory implements RequestFactoryInterface
{
    #[Pure] public function __construct(
        private readonly StreamFactoryInterface $streamFactory = new StreamFactory,
        private readonly UriFactoryInterface    $uriFactory = new UriFactory,
    ) {}

    public function createRequest(
        string $method,
               $uri,
        string $protocolVersion = '2',
        array  $headers = []
    ): RequestInterface
    {
        $body = $this->streamFactory->createTempFileStream();

        if ($uri instanceof UriInterface === false) {
            $uri = $this->uriFactory->createUri($uri);
        }

        return new Request($body, $method, $uri, (string)$uri, $protocolVersion, $headers);
    }
}
