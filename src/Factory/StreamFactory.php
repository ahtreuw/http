<?php declare(strict_types=1);

namespace Http\Factory;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Http\Message\StreamDecorator;
use Http\Message\Stream;

class StreamFactory implements StreamFactoryInterface
{

    /**
     * Create a new stream from a string.
     *
     * The stream SHOULD be created with a temporary resource.
     *
     * @param string $content String content with which to populate the stream.
     * @param array $metadata
     *
     * @return StreamInterface
     */
    #[Pure] public function createStream(string $content = '', array $metadata = []): StreamInterface
    {
        return new StreamDecorator(function () use ($content, $metadata) {
            $stream = $this->createTempFileStream($metadata);
            $stream->write($content);
            $stream->rewind();
            return $stream;
        });
    }

    /**
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the given mode, which may be any mode supported by the `fopen` function.
     *
     * The `$filename` MAY be any string supported by `fopen()`.
     *
     * @param string $filename Filename or stream URI to use as basis of stream.
     * @param string $mode Mode with which to open the underlying filename/stream.
     * @param array $metadata
     *
     * @return StreamInterface
     *
     * @throws RuntimeException If the file cannot be opened.
     * @throws InvalidArgumentException If the mode is invalid.
     */
    #[Pure] public function createStreamFromFile(
        string $filename,
        string $mode = self::READ_EXISTING,
        array  $metadata = []
    ): StreamInterface
    {
        return new StreamDecorator(function () use ($filename, $mode, $metadata) {
            $resource = $this->createResource($filename, $mode);
            $metadata = $this->prepareMetadata($resource, $metadata);
            return new Stream(stream: $resource, metadata: $metadata);
        });
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param resource $resource PHP resource to use as basis of stream.
     * @param array $metadata
     *
     * @return StreamInterface
     */
    public function createStreamFromResource($resource, array $metadata = []): StreamInterface
    {
        $metadata = $this->prepareMetadata($resource, $metadata);

        // The stream MUST be readable and may be writable.
        if ($metadata['readable'] === false) {
            throw new RuntimeException(sprintf('Stream is not readable: %s', $metadata['uri'] ?? ''));
        }

        return new Stream(stream: $resource, metadata: $metadata);
    }

    /**
     * Create stream from file or URL.
     *
     * @param string $filename
     * @param string $mode
     *
     * @return resource
     *
     * @throws RuntimeException
     */
    public function createResource(string $filename, string $mode = 'r')
    {
        set_error_handler(function (int $errno, string $errstr) use (&$exception, $filename, $mode): bool {
            $args = [$filename, $mode, $errstr];
            $exception = new RuntimeException(sprintf('Unable to open "%s" using mode "%s": %s', ...$args));
            return true;
        });

        $stream = fopen($filename, $mode);

        restore_error_handler();

        if (is_resource($stream)) {
            return $stream;
        }

        throw $exception ?: new RuntimeException(sprintf('Unable to open "%s" using mode "%s"', $filename, $mode));
    }

    #[Pure] public function createRequestBodyStream(array $metadata = []): StreamInterface
    {
        return $this->createStreamFromFile(self::INPUT, self::READ_EXISTING, $metadata);
    }

    #[Pure] public function createOutputBufferStream(array $metadata = []): StreamInterface
    {
        return $this->createStreamFromFile(self::OUTPUT, self::READ_WRITE_APPEND_ONLY, $metadata);
    }

    #[Pure] public function createInMemoryStream(array $metadata = []): StreamInterface
    {
        return $this->createStreamFromFile(self::MEMORY, self::READ_WRITE_TRUNCATE, $metadata);
    }

    #[Pure] public function createTempFileStream(array $metadata = []): StreamInterface
    {
        return $this->createStreamFromFile(self::TEMP, self::READ_WRITE_TRUNCATE, $metadata);
    }

    #[Pure] public function createSTDINStream(array $metadata = []): StreamInterface
    {
        return $this->createStreamFromFile(self::STDIN, self::READ_EXISTING, $metadata);
    }

    #[Pure] public function createSTDOUTStream(array $metadata = []): StreamInterface
    {
        return $this->createStreamFromFile(self::STDOUT, self::WRITE_APPEND_ONLY, $metadata);
    }

    #[Pure] public function createSTDERRStream(array $metadata = []): StreamInterface
    {
        return $this->createStreamFromFile(self::STDERR, self::WRITE_TRUNCATE, $metadata);
    }

    private function prepareMetadata($resource, array $metadata): array
    {
        $metadata = $metadata + stream_get_meta_data($resource);

        $rwMode = preg_replace("/[^rwaxc+]/", '', $metadata['mode'] ?? '');

        $metadata['mediatype'] = $metadata['mediatype'] ?? @mime_content_type($resource);
        $metadata['seekable'] = (bool)$metadata['seekable'] ?? false;
        $metadata['readable'] = (bool)preg_match(self::READABLE_MODES, $rwMode);
        $metadata['writable'] = (bool)preg_match(self::WRITABLE_MODES, $rwMode);

        return $metadata;
    }
}
