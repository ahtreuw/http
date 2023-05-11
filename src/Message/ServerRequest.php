<?php declare(strict_types=1);

namespace Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    public function __construct(
        string                    $method,
        UriInterface              $uri,
        StreamInterface           $body,
        null|string               $requestTarget = null,
        string                    $protocolVersion = '1.1',
        array                     $headers = [],
        private array             $attributes = [],
        private array             $cookieParams = [],
        private array|null|object $parsedBody = null,
        private array             $queryParams = [],
        private array             $serverParams = [],
        private array             $uploadedFiles = []
    )
    {
        parent::__construct($body, $method, $uri, $requestTarget, $protocolVersion, $headers);
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        if ($this->cookieParams === $cookies) {
            return $this;
        }
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        if ($this->queryParams === $query) {
            return $this;
        }
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        if ($this->uploadedFiles === $uploadedFiles) {
            return $this;
        }
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    public function getParsedBody(): object|array|null
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        if ($this->parsedBody === $data) {
            return $this;
        }
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        if (array_key_exists($name, $this->attributes) && $this->attributes[$name] === $value) {
            return $this;
        }
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }
}
