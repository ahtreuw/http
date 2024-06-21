<?php declare(strict_types=1);

namespace Http\Factory;

use Psr\Http\Message\UriInterface;

interface UriFactoryInterface extends \Psr\Http\Message\UriFactoryInterface
{
    public function createUriFromServerParams(array $serverParams): UriInterface;
    
    public function createUriFromGlobals(): UriInterface;
}
