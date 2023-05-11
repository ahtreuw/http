<?php declare(strict_types=1);

namespace Http\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Http\HTTP;

class Response extends Message implements ResponseInterface
{
    public function __construct(
        StreamInterface $body,
        private int     $code = 200,
        private string  $reasonPhrase = '',
        string          $protocolVersion = '1.1',
        array           $headers = [],
    )
    {
        parent::__construct($body, $protocolVersion, $headers);
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->code = $code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function getReasonPhrase(): string
    {
        if (!$this->reasonPhrase && array_key_exists($this->code, HTTP::PHRASES)) {
            return HTTP::PHRASES[$this->code];
        }
        return $this->reasonPhrase;
    }
}
