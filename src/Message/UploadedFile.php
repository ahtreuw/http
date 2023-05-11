<?php declare(strict_types=1);

namespace Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Throwable;

class UploadedFile implements UploadedFileInterface
{
    public function __construct(
        private UploadFileInterface $upload,
        private StreamInterface     $stream,
        private null|int            $size = null,
        private int                 $error = UPLOAD_ERR_OK,
        private null|string         $clientFilename = null,
        private null|string         $clientMediaType = null,
    )
    {
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    public function moveTo(string $targetPath): void
    {
        $uploadedFile = $this->stream->getMetadata('uri');

        if ($this->upload->isUploadedFile($uploadedFile) === false) {
            throw new RuntimeException(sprintf('File must be uploaded via HTTP POST: %s', $uploadedFile));
        }

        try {
            $this->upload->moveFile($this->stream, $targetPath);
        } catch (RuntimeException|InvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('Error during the move operation: %s', $e->getMessage()), 0, $e);
        }
    }

    public function getSize(): ?int
    {
        return is_null($this->size) ? $this->stream->getSize() : $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return is_null($this->clientFilename) ? $this->stream->getMetadata('uri') : $this->clientFilename;
    }

    public function getClientMediaType(): ?string
    {
        return is_null($this->clientMediaType) ? $this->stream->getMetadata('mediatype') : $this->clientMediaType;
    }
}
