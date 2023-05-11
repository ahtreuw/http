<?php declare(strict_types=1);

namespace Http\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;
use Http\HTTP;

class ServerRequestException extends Exception implements ServerRequestExceptionInterface
{
    public const STATUS_CODE = 400;

    #[Pure] public function __construct(null|string $message = '', ?int $code = 0, null|Throwable $previous = null)
    {
        $message = $message ?: HTTP::PHRASES[static::STATUS_CODE] ?? 'Bad Request';
        parent::__construct($message, $code ?: self::STATUS_CODE, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }
}
