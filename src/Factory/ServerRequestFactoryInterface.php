<?php declare(strict_types=1);

namespace Http\Factory;

use Psr\Http\Message\ServerRequestInterface;

interface ServerRequestFactoryInterface extends \Psr\Http\Message\ServerRequestFactoryInterface
{
    public function createServerRequest(
        string $method,
               $uri,
        array $serverParams = [],
        array $attributes = []
    ): ServerRequestInterface;

    public function createServerRequestFromGlobals(): ServerRequestInterface;
}