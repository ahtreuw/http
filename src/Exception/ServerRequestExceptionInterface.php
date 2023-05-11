<?php declare(strict_types=1);

namespace Http\Exception;

use Psr\Http\Client\ClientExceptionInterface;

interface ServerRequestExceptionInterface extends ClientExceptionInterface
{
    public function getStatusCode(): int;
}