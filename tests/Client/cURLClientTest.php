<?php declare(strict_types=1);

namespace Http\Client;

use Http\Factory\ResponseFactoryInterface;
use Http\Factory\StreamFactoryInterface;
use Http\HTTP;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @property cURLClient $client
 * @property cURLRequestInterface|MockObject $httpRequest
 * @property StreamFactoryInterface|MockObject $streamFactory
 * @property ResponseFactoryInterface|MockObject $responseFactory
 * @property MockObject|RequestInterface $request
 * @property MockObject|ResponseInterface $response
 * @property MockObject|StreamInterface $requestStream
 * @property MockObject|StreamInterface $responseStream
 */
class cURLClientTest extends TestCase
{

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->httpRequest = $this->createMock(cURLRequestInterface::class);
        $this->requestStream = $this->createMock(StreamInterface::class);
        $this->responseStream = $this->createMock(StreamInterface::class);

        $this->client = new cURLClient(
            $this->responseFactory,
            $this->streamFactory,
            $this->httpRequest
        );
    }

    /**
     * @dataProvider sendRequestData
     * @throws ClientExceptionInterface
     */
    public function testSendRequest(
        string|bool $result,
        array       $info,
        array       $responseHeaders,
        string      $protocolVersion
    ): void
    {
        $this->httpRequest->expects($this->once())->method('curl_init')->willReturn(true);
        $this->httpRequest->expects($this->once())->method('curl_setopt_array')->willReturn(true);
        $this->request->expects($this->once())->method('getBody')->willReturn($this->requestStream);
        $this->httpRequest->expects($this->once())->method('curl_exec')->willReturn($result);

        $this->httpRequest->expects($this->any())->method('curl_errno')->willReturn(0);
        $this->httpRequest->expects($this->any())->method('curl_error')->willReturn('');

        $this->httpRequest->expects($this->once())->method('curl_getinfo')->willReturn($info);

        $this->streamFactory->expects($this->once())->method('createStream')
            ->with(is_bool($result) ? '' : $result, ['curl' => $info])
            ->willReturn($this->responseStream);

        $this->responseFactory->expects($this->once())->method('createResponse')
            ->with(
                $info['http_code'],
                HTTP::PHRASES[$info['http_code']] ?? '',
                $protocolVersion,
                $responseHeaders,
                $this->responseStream
            )
            ->willReturn($this->response);

        $response = $this->client->sendRequest($this->request);
        self::assertSame($this->response, $response);

        $this->httpRequest->expects($this->once())->method('curl_close');
        $this->client->__destruct();
    }

    public static function sendRequestData(): array
    {
        return [
            [true, ['key' => 'value', 'http_code' => 200], [], '1.1']
        ];
    }

}
