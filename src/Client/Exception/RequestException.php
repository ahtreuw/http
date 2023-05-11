<?php declare(strict_types=1);

namespace Http\Client\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class RequestException extends Exception implements RequestExceptionInterface
{
    protected RequestInterface $request;

    #[Pure] public function __construct(
        RequestInterface $request,
        null|string      $message = '',
        int              $code = 0,
        Throwable|null   $previous = null
    )
    {
        $this->request = $request;
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
