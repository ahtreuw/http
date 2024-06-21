<?php declare(strict_types=1);

namespace Http\Factory;

use JetBrains\PhpStorm\Pure;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;
use Http\Message\Response;

class ResponseFactory implements ResponseFactoryInterface
{
    #[Pure] public function __construct(
        private readonly StreamFactoryInterface $streamFactory = new StreamFactory,
        private readonly int                    $jsonEncodeFlags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    ) {}

    public function createResponse(
        int                                    $code = 200,
        string                                 $reasonPhrase = '',
        string                                 $protocolVersion = '1.1',
        array                                  $headers = [],
        StreamInterface|Stringable|string|null $body = null
    ): ResponseInterface
    {
        return new Response($this->prepareBody($body), $code, $reasonPhrase, $protocolVersion, $headers);
    }

    public function createJson(
        int                      $code = 200,
        object|array|string|null $body = null,
        array                    $headers = [],
        string                   $reasonPhrase = '',
        string                   $protocolVersion = '1.1'
    ): ResponseInterface
    {
        $response = new Response($this->prepareJsonBody($body), $code, $reasonPhrase, $protocolVersion, $headers);

        if ($response->hasHeader('Content-type') === false) {
            $response = $response->withHeader('Content-type', 'application/json;charset=utf-8');
        }

        return $response;
    }

    public function createHtml(
        int                    $code = 200,
        ?string                $filename = null,
        array                  $data = [],
        Stringable|string|null $body = null,
        array                  $headers = [],
        string                 $reasonPhrase = '',
        string                 $protocolVersion = '1.1'
    ): ResponseInterface
    {
        $body = $filename ? $this->createBodyFromFile($filename, $data) : $this->prepareBody($body);

        $response = new Response($body, $code, $reasonPhrase, $protocolVersion, $headers);

        if ($response->hasHeader('Content-type') === false) {
            $response = $response->withHeader('Content-type', 'text/html;charset=utf-8');
        }

        return $response;
    }


    #[Pure] private function prepareBody(null|string|StreamInterface|Stringable $body): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }
        if (is_string($body) || $body instanceof Stringable) {
            return $this->streamFactory->createStream(strval($body));
        }
        return $this->streamFactory->createTempFileStream();
    }

    private function prepareJsonBody(object|bool|array|string|null $body): StreamInterface
    {
        if ($body instanceof JsonSerializable) {
            return $this->streamFactory->createStream(json_encode($body, $this->jsonEncodeFlags));
        }
        if (is_array($body)) {
            return $this->streamFactory->createStream(json_encode($body, $this->jsonEncodeFlags));
        }
        if (is_object($body) && $body instanceof StreamInterface === false) {
            $body = json_encode(get_object_vars($body), $this->jsonEncodeFlags);
        }
        return $this->prepareBody($body);
    }

    private function createBodyFromFile(string $filename, array $data): StreamInterface
    {
        $read = static function (string $filename, array $data): string {
            extract($data);
            ob_start();
            require $filename;
            return ob_get_clean();
        };
        return $this->streamFactory->createStream($read($filename, $data));
    }
}
