<?php declare(strict_types=1);

namespace Http\Exception;

use JetBrains\PhpStorm\Pure;
use Throwable;

class BadRequestException extends ServerRequestException
{
    public const STATUS_CODE = 400;

    #[Pure] public function __construct(?string $message = null, ?int $code = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}