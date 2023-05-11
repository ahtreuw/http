<?php declare(strict_types=1);

namespace Http\Message;

use Closure;
use Error;
use Psr\Http\Message\StreamInterface;

/**
 * @property StreamInterface $stream
 */
class StreamDecorator implements StreamInterface
{
    public function __construct(private Closure $create)
    {
    }

    public function __get(string $name): StreamInterface
    {
        if ($name !== 'stream') {
            throw new Error(sprintf(' Undefined property: %s::$%s', static::class, $name));
        }
        return $this->stream = ($this->create)();
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }
        throw new Error(sprintf('Call to undefined method %s::%s()', static::class, $name));
    }

    public function close(): void
    {
        $this->stream->close();
    }

    public function detach()
    {
        return $this->stream->detach();
    }

    public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    public function tell(): int
    {
        return $this->stream->tell();
    }

    public function eof(): bool
    {
        return $this->stream->eof();
    }

    public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->stream->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->stream->rewind();
    }

    public function isWritable(): bool
    {
        return $this->stream->isWritable();
    }

    public function write(string $string): int
    {
        return $this->stream->write($string);
    }

    public function isReadable(): bool
    {
        return $this->stream->isReadable();
    }

    public function read(int $length): string
    {
        return $this->stream->read($length);
    }

    public function getContents(): string
    {
        return $this->stream->getContents();
    }

    public function getMetadata(?string $key = null)
    {
        return $this->stream->getMetadata($key);
    }

    public function __toString(): string
    {
        return $this->stream->__toString();
    }
}
