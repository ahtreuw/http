<?php declare(strict_types=1);

namespace Http\Client\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class NetworkException extends Exception implements NetworkExceptionInterface
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
