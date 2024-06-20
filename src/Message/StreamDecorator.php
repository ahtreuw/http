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
    private null|StreamInterface $streamInterface = null;

    public function __construct(private Closure $create)
    {
    }

    public function __get(string $name): StreamInterface
    {
        if ($name !== 'stream') {
            throw new Error(sprintf(' Undefined property: %s::$%s', static::class, $name));
        }
        return $this->getStreamInterface();
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
        $this->getStreamInterface()->close();
    }

    public function detach()
    {
        return $this->getStreamInterface()->detach();
    }

    public function getSize(): ?int
    {
        return $this->getStreamInterface()->getSize();
    }

    public function tell(): int
    {
        return $this->getStreamInterface()->tell();
    }

    public function eof(): bool
    {
        return $this->getStreamInterface()->eof();
    }

    public function isSeekable(): bool
    {
        return $this->getStreamInterface()->isSeekable();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->getStreamInterface()->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->getStreamInterface()->rewind();
    }

    public function isWritable(): bool
    {
        return $this->getStreamInterface()->isWritable();
    }

    public function write(string $string): int
    {
        return $this->getStreamInterface()->write($string);
    }

    public function isReadable(): bool
    {
        return $this->getStreamInterface()->isReadable();
    }

    public function read(int $length): string
    {
        return $this->getStreamInterface()->read($length);
    }

    public function getContents(): string
    {
        return $this->getStreamInterface()->getContents();
    }

    public function getMetadata(?string $key = null)
    {
        return $this->getStreamInterface()->getMetadata($key);
    }

    public function __toString(): string
    {
        return $this->getStreamInterface()->__toString();
    }

    private function getStreamInterface(): StreamInterface
    {
        if (is_null($this->streamInterface)) {
            $this->streamInterface = ($this->create)();
        }
        return $this->streamInterface;
    }
}
