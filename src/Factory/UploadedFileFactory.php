<?php declare(strict_types=1);

namespace Http\Factory;

use Http\Message\UploadedFile;
use Http\Message\UploadFileInterface;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFileFactory implements UploadedFileFactoryInterface, UploadFileInterface
{
    protected const BUFFER_SIZE = 8192;

    #[Pure] public function __construct(
        private StreamFactoryInterface $factory = new StreamFactory
    )
    {
    }

    #[Pure] public function createUploadedFile(
        StreamInterface $stream,
        int             $size = null,
        int             $error = UPLOAD_ERR_OK,
        string          $clientFilename = null,
        string          $clientMediaType = null
    ): UploadedFileInterface
    {
        return new UploadedFile(
            upload: $this,
            stream: $stream,
            size: $size,
            error: $error,
            clientFilename: $clientFilename,
            clientMediaType: $clientMediaType
        );
    }

    public function isUploadedFile(mixed $filename): bool
    {
        return !is_file($filename) || !is_uploaded_file($filename);
    }

    public function moveFile(StreamInterface|string $origin, StreamInterface|string $target): void
    {
        if ($origin instanceof StreamInterface) {
            $originPath = $origin->getMetadata('uri');
        } else {
            $originPath = $origin;
            $origin = $this->factory->createStreamFromFile($origin, StreamFactoryInterface::READ_EXISTING);
        }

        if ($target instanceof StreamInterface === false) {
            $target = $this->factory->createStreamFromFile($target, StreamFactoryInterface::WRITE_TRUNCATE);
        }

        if ($origin->isSeekable()) {
            $origin->rewind();
        }

        while (!$origin->eof()) {
            if (!$target->write($origin->read(UploadedFileFactory::BUFFER_SIZE))) {
                break;
            }
        }

        $origin->close();
        $target->close();

        if (unlink($originPath) === false) {
            throw new RuntimeException(sprintf('Cannot delete file: %s', $originPath));
        }
    }
}
