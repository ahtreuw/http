<?php declare(strict_types=1);

namespace Http\Factory;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;

interface ResponseFactoryInterface extends \Psr\Http\Message\ResponseFactoryInterface
{

    public function createResponse(
        int                                    $code = 200,
        string                                 $reasonPhrase = '',
        string                                 $protocolVersion = '1.1',
        array                                  $headers = [],
        null|string|Stringable|StreamInterface $body = null
    ): ResponseInterface;


    public function createJson(
        int                      $code = 200,
        null|string|array|object $body = null,
        array                    $headers = [],
        string                   $reasonPhrase = '',
        string                   $protocolVersion = '1.1'
    ): ResponseInterface;

    public function createHtml(
        int                    $code = 200,
        null|string            $filename = null,
        array                  $data = [],
        null|string|Stringable $body = null,
        array                  $headers = [],
        string                 $reasonPhrase = '',
        string                 $protocolVersion = '1.1'
    ): ResponseInterface;
}