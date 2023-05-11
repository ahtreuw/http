<?php declare(strict_types=1);

namespace Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    /** @var string[] */
    private array $headerNames = [];

    public function __construct(
        private StreamInterface $body,
        private string          $protocolVersion,
        /** @var array<string[]>  */
        private array           $headers,
    )
    {
        $this->headers = $this->prepareHeaders($headers);
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->headerNames);
    }

    public function getHeader(string $name): array
    {
        $headerName = $this->headerNames[strtolower($name)] ?? null;
        return $headerName ? $this->headers[$headerName] : [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $new = clone $this;
        $new->setHeader($name, $value);
        return $new;
    }

    protected function setHeader(string $name, $value): void
    {
        $headerKey = strtolower($name);
        $existingName = $this->headerNames[$headerKey] ?? null;

        if ($existingName) {
            unset($this->headers[$existingName]);
        }

        $this->headerNames[$headerKey] = $name;
        $this->headers[$name] = $this->normalizeHeaderValue($value);

        if (empty($this->headers[$name])) {
            unset($this->headers[$name], $this->headerNames[$headerKey]);
        }
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $headerKey = strtolower($name);
        $headerName = $this->headerNames[$headerKey] ?? $name;

        $new = clone $this;
        $new->headerNames[$headerKey] = $headerName;

        $new->headers[$headerName] = $new->normalizeHeaderValue(array_merge(
            $new->headers[$headerName] ?? [], $new->normalizeHeaderValue($value)
        ));

        if (empty($new->headers[$name])) {
            unset($new->headers[$headerName], $new->headerNames[$headerKey]);
        }

        return $new;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $headerKey = strtolower($name);
        $headerName = $this->headerNames[$headerKey] ?? null;

        $new = clone $this;

        if ($headerName) {
            unset($new->headers[$headerName], $new->headerNames[$headerKey]);
        }

        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    private function prepareHeaders(array $headers): array
    {
        foreach ($headers as $name => $value) {
            $normalizedValue = $this->normalizeHeaderValue($value);
            if (empty($normalizedValue)) {
                unset($headers[$name]);
                continue;
            }
            $this->headerNames[strtolower($name)] = $name;
            $headers[$name] = $normalizedValue;
        }
        return $headers;
    }

    private function normalizeHeaderValue(array|string $value): array
    {
        if (is_string($value)) {
            return $this->normalizeHeaderValue(explode(';', $value));
        }
        return array_filter(array_unique(array_map('trim', $value)));
    }
}
