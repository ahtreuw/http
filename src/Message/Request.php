<?php declare(strict_types=1);

namespace Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    public function __construct(
        StreamInterface      $body,
        private string       $method,
        private UriInterface $uri,
        private null|string  $requestTarget = null,
        string               $protocolVersion = '2',
        array                $headers = [],
    )
    {
        parent::__construct($body, $protocolVersion, $headers);
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): RequestInterface
    {
        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $new = clone $this;
        $new->uri = $uri;

        if ($preserveHost === false || $this->hasHeader('host') === false) {
            $new->updateHost();
        }

        return $new;
    }

    private function updateHost(): void
    {
        if (($host = $this->uri->getHost()) == '') {
            return;
        }
        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }
        $this->setHeader('Host', $host);
    }
}
