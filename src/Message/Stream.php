<?php declare(strict_types=1);

namespace Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

class Stream implements StreamInterface
{
    private const CODE_CONSTRUCT = 1;
    private const CODE_TELL = 2;
    private const CODE_EOF = 4;
    private const CODE_SEEK = 8;
    private const CODE_WRITE = 16;
    private const CODE_READ = 32;
    private const CODE_GET_CONTENTS = 64;

    private const STATUS_ACTIVE = 'active';
    private const STATUS_CLOSED = 'closed';
    private const STATUS_DETACHED = 'detached';

    /**
     * @var null|resource
     */
    private $stream;

    private null|string $status = null;

    /**
     * @param resource $stream
     */
    public function __construct($stream, private array $metadata = [])
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource', Stream::CODE_CONSTRUCT);
        }
        $this->stream = $stream;
        $this->status = Stream::STATUS_ACTIVE;
    }

    public function close(): void
    {
        if ($this->status === Stream::STATUS_ACTIVE) {
            fclose($this->stream);
        }
        $this->status = Stream::STATUS_CLOSED;
        $this->detach();
    }

    public function detach()
    {
        if ($this->status === Stream::STATUS_DETACHED) {
            return null;
        }

        $this->status = Stream::STATUS_DETACHED;

        if (isset($this->stream)) {
            $resource = $this->stream;
            unset($this->stream);
            return $resource;
        }

        return null;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getSize(): ?int
    {
        if (false === is_null($size = $this->getMetadata('size'))) {
            return $size;
        }

        if ($this->status !== Stream::STATUS_ACTIVE) {
            return null;
        }

        if ($uri = $this->getMetadata('uri')) {
            clearstatcache(true, $uri);
        }

        $stats = fstat($this->stream);

        $this->metadata['size'] = $stats ? $stats['size'] ?? null : null;

        return $this->metadata['size'];
    }

    public function tell(): int
    {
        $this->checkResourceStatus(Stream::CODE_TELL);

        $result = ftell($this->stream);

        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position', Stream::CODE_TELL);
        }

        return $result;
    }

    public function eof(): bool
    {
        $this->checkResourceStatus(Stream::CODE_EOF);
        return feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return (bool)$this->getMetadata('seekable');
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->checkResourceStatus(Stream::CODE_SEEK);

        if ($this->isSeekable() === false) {
            throw new RuntimeException('Stream is not seekable', Stream::CODE_SEEK);
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException(sprintf('Unable to seek to stream position %d with whence %d', $offset, $whence), Stream::CODE_SEEK);
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return (bool)$this->getMetadata('writable');
    }

    public function write(string $string): int
    {
        $this->checkResourceStatus(Stream::CODE_WRITE);

        if ($this->isWritable() === false) {
            throw new RuntimeException('Cannot write to a non-writable stream', Stream::CODE_WRITE);
        }

        $result = fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream', Stream::CODE_WRITE);
        }

        if (false === is_null($this->getMetadata('size'))) {
            $this->metadata['size'] += strlen($string);
        }

        return $result;
    }

    public function isReadable(): bool
    {
        return (bool)$this->getMetadata('readable');
    }

    public function read(int $length): string
    {
        $this->checkResourceStatus(Stream::CODE_READ);

        if ($this->isReadable() === false) {
            throw new RuntimeException('Cannot read from non-readable stream', Stream::CODE_READ);
        }
        if ($length < 0) {
            throw new InvalidArgumentException('Length parameter cannot be negative', Stream::CODE_READ);
        }
        if (0 === $length) {
            return '';
        }
        try {
            $string = fread($this->stream, $length);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to read from stream', Stream::CODE_READ, $e);
        }
        if (false === $string) {
            throw new RuntimeException('Unable to read from stream', Stream::CODE_READ);
        }
        return $string;
    }

    public function getContents(): string
    {
        set_error_handler(function (int $errno, string $errstr) use (&$exception): bool {
            $exception = new RuntimeException(sprintf('Unable to read stream contents: %s', $errstr), Stream::CODE_GET_CONTENTS);
            return true;
        });

        $contents = stream_get_contents($this->stream);

        if (is_string($contents)) {
            return $contents;
        }

        restore_error_handler();

        throw $exception ?: new RuntimeException('Unable to read stream contents', Stream::CODE_GET_CONTENTS);
    }

    public function getMetadata(?string $key = null)
    {
        return is_null($key) ? $this->metadata : $this->metadata[$key] ?? null;
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (Throwable) {
        }
        return '';
    }

    private function checkResourceStatus(int $code): void
    {
        if ($this->status === Stream::STATUS_DETACHED) {
            throw new RuntimeException('Stream is detached', $code);
        }
        if ($this->status === Stream::STATUS_CLOSED) {
            throw new RuntimeException('Stream is closed', $code);
        }
    }
}
