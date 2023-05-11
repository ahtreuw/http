<?php declare(strict_types=1);

namespace Http\Client;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Throwable;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\RequestException;
use Http\HTTP;
use Http\Factory\ResponseFactory;
use Http\Factory\ResponseFactoryInterface;
use Http\Factory\StreamFactory;
use Http\Factory\StreamFactoryInterface;

class cURLClient implements ClientInterface
{
    public const DEFAULT_OPTIONS = [
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => true
    ];

    #[Pure] public function __construct(
        protected ResponseFactoryInterface $responseFactory = new ResponseFactory,
        protected StreamFactoryInterface   $streamFactory = new StreamFactory,
        protected cURLRequestInterface     $cURL = new cURLRequest
    )
    {
    }

    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     *
     * @throws ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $this->createCurlHandle($request);

            $options = $this->getRequestOptions($request);

            if ($this->cURL->curl_setopt_array($options) === false) {
                $message = 'InValid options to set for cURL transfer. options: %s';
                throw new RequestException($request, sprintf($message, print_r($options, true)));
            }

            $result = $this->cURL->curl_exec();
            $errno = $this->cURL->curl_errno();

            if ($errno !== CURLE_OK) {
                $this->handleError($request, $this->cURL->curl_error(), $errno);
            }

            return $this->createResponse($result, $this->cURL->curl_getinfo());

        } catch (ClientExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RequestException($request, 'Error while create cURL request', 0, $exception);
        }
    }

    /**
     * @throws RequestException
     */
    public function createCurlHandle(RequestInterface $request): void
    {
        $result = $this->cURL->curl_init();

        if ($result === false) {
            throw new RequestException($request, 'Unable to initialize a cURL session');
        }
    }

    public function close(): void
    {
        $this->cURL->curl_close();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getRequestOptions(RequestInterface $request): array
    {
        $opts = $request->getBody()->getMetadata('curl-options') ?? self::DEFAULT_OPTIONS;

        $opts[CURLOPT_URL] = $request->getRequestTarget();
        $opts[CURLOPT_CUSTOMREQUEST] = $request->getMethod();

        $opts[CURLOPT_HTTPHEADER] = $this->getRequestHeaders($request);
        $opts[CURLOPT_POSTFIELDS] = $this->getPostFieldsBody($request);

        $opts[CURLOPT_HTTP_VERSION] = $this->getRequestProtocolVersion($request);
        $opts[CURLOPT_RETURNTRANSFER] = HTTP::RESPONSE_HAS_BODY[$request->getMethod()];

        return $opts;
    }

    /**
     * @return array<string>
     */
    public function getRequestHeaders(RequestInterface $request): array
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[$name] = $request->getHeaderLine($name);
        }
        return $headers;
    }

    public function getPostFieldsBody(RequestInterface $request): null|string
    {
        $hasRequestBody = HTTP::REQUEST_HAS_BODY[$request->getMethod()] ?? HTTP::NO;

        if ($hasRequestBody === HTTP::NO) {
            return null;
        }

        if ($request->getBody()->getSize()) {
            return $request->getBody()->__toString();
        }

        return null;
    }

    public function createResponse(bool|string $result, array $info): ResponseInterface
    {
        [$head, $body] = $this->getRawHeadAndBody($result, $info);

        return $this->responseFactory->createResponse(
            code: $info['http_code'],
            reasonPhrase: HTTP::PHRASES[$info['http_code']] ?? '',
            protocolVersion: $this->getProtocolVersionFromRawHead($head),
            headers: $this->getResponseHeaders($head),
            body: $body,
        );
    }

    public function getProtocolVersionFromRawHead(string $rawHead): string
    {
        return substr($rawHead, 5, strpos($rawHead, ' ') - 5) ?: '1.1';
    }

    public function getResponseHeaders(string $rawHead): array
    {
        $rows = explode("\n", $rawHead);
        $headers = [];
        foreach ($rows as $row) {
            [$name, $value] = array_pad(explode(':', $row, 2), 2, null);
            if ($value === null) {
                continue;
            }
            $headers[$name] = $value;
        }
        return $headers;
    }

    /**
     * @param bool|string $result
     * @param array $info
     * @return array{string,StreamInterface}
     */
    public function getRawHeadAndBody(bool|string $result, array $info): array
    {
        if (is_bool($result)) {
            return ['', $this->createBody('', $info)];
        }

        return [
            trim(substr($result, 0, $info['header_size'] ?? 0)),
            $this->createBody(trim(substr($result, $info['header_size'] ?? 0)), $info)
        ];
    }

    public function createBody(string $body, array $info): StreamInterface
    {
        return $this->streamFactory->createStream($body, ['curl' => $info]);
    }

    #[Pure] public function getRequestProtocolVersion(RequestInterface $request): int
    {
        return match ($request->getProtocolVersion()) {
            '1.0', '1' => CURL_HTTP_VERSION_1_0,
            '1.1' => CURL_HTTP_VERSION_1_1,
            '2' => CURL_HTTP_VERSION_2,
            '2.0' => CURL_HTTP_VERSION_2_0,
            default => CURL_HTTP_VERSION_NONE
        };
    }

    /**
     * @throws NetworkException
     * @throws RequestException
     */
    public function handleError(RequestInterface $request, string $message, int $errno): void
    {
        switch ($errno) {

            // Basic network errors
            case CURLE_SEND_ERROR:
            case CURLE_RECV_ERROR:

                // Connection errors
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_COULDNT_RESOLVE_PROXY:

                // Timeout errors
            case CURLE_OPERATION_TIMEOUTED:
            case CURLE_OPERATION_TIMEDOUT:

                // SSH errors
            case CURLE_SSH:

                // SSL errors
            case CURLE_SSL_CONNECT_ERROR:
            case CURLE_SSL_PEER_CERTIFICATE:
            case CURLE_SSL_ENGINE_NOTFOUND:
            case CURLE_SSL_ENGINE_SETFAILED:
            case CURLE_SSL_CERTPROBLEM:
            case CURLE_SSL_CIPHER:
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PINNEDPUBKEYNOTMATCH:
            case CURLE_SSL_CACERT_BADFILE:

                throw new NetworkException($request, $message, $errno);
        }

        throw new RequestException($request, $message, $errno);
    }
}
