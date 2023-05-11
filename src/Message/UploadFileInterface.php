<?php declare(strict_types=1);

namespace Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

interface UploadFileInterface
{

    /**
     * Tells whether the file was uploaded via HTTP POST
     * @link https://php.net/manual/en/function.is-uploaded-file.php
     */
    public function isUploadedFile(mixed $filename): bool;

    /**
     * Moves an uploaded file to a new location
     *
     * @throws InvalidArgumentException if the $targetPath specified is invalid.
     * @throws RuntimeException on any error during the move operation.
     */
    public function moveFile(StreamInterface|string $origin, StreamInterface|string $target): void;
}
