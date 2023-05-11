<?php declare(strict_types=1);

namespace Http\Message;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

class UploadedFileTest extends TestCase
{

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testStreamAndGetters(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $upload = $this->createMock(UploadFileInterface::class);

        $upload->expects($this->never())->method('isUploadedFile');
        $upload->expects($this->never())->method('moveFile');

        $stream->expects($this->any())->method('getSize')->willReturn(13);
        $stream->expects($this->any())->method('getMetadata')->willReturnCallback(function ($meta) {
            return match ($meta) {
                'uri' => 'uploaded-file-name',
                'mediatype' => 'uploaded-file-type',
            };
        });

        $uploadedFile = new UploadedFile($upload, $stream);

        self::assertSame($stream, $uploadedFile->getStream());

        self::assertEquals(13, $uploadedFile->getSize());
        self::assertEquals('uploaded-file-name', $uploadedFile->getClientFilename());
        self::assertEquals('uploaded-file-type', $uploadedFile->getClientMediaType());
        self::assertEquals(UPLOAD_ERR_OK, $uploadedFile->getError());

        $uploadedFile = new UploadedFile($upload, $stream, 15
            , UPLOAD_ERR_CANT_WRITE, 'client-filename', 'client-media-type');

        self::assertSame($stream, $uploadedFile->getStream());

        self::assertEquals(15, $uploadedFile->getSize());
        self::assertEquals('client-filename', $uploadedFile->getClientFilename());
        self::assertEquals('client-media-type', $uploadedFile->getClientMediaType());
        self::assertEquals(UPLOAD_ERR_CANT_WRITE, $uploadedFile->getError());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testMoveTo(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $upload = $this->createMock(UploadFileInterface::class);

        $upload->expects($this->once())->method('isUploadedFile')->willReturn(true);
        $upload->expects($this->once())->method('moveFile')->with($stream, 'target-path');

        $stream->expects($this->any())->method('getMetadata')->willReturnCallback(function ($meta) {
            return match ($meta) {
                'uri' => 'uploaded-file-name',
                'mediatype' => 'uploaded-file-type',
            };
        });

        $uploadedFile = new UploadedFile($upload, $stream);
        $uploadedFile->moveTo('target-path');
    }

    /**
     * @dataProvider exceptionsProvider
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testMoveToExceptions(bool $isUploadedFile, string $ee, string $eem, Throwable $e): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $upload = $this->createMock(UploadFileInterface::class);

        $upload->expects($this->once())->method('isUploadedFile')->willReturn($isUploadedFile);
        $upload->expects($this->any())->method('moveFile')->willReturnCallback(function () use ($e) {
            throw $e;
        });

        $stream->expects($this->any())->method('getMetadata')->willReturnCallback(function ($meta) {
            return match ($meta) {
                'uri' => 'uploaded-file-name',
                'mediatype' => 'uploaded-file-type',
            };
        });

        $uploadedFile = new UploadedFile($upload, $stream);

        $this->expectException($ee);
        $this->expectExceptionMessage($eem);

        $uploadedFile->moveTo('target-path');
    }

    public static function exceptionsProvider(): array
    {
        return [
            [true, RuntimeException::class, 'my-xc', new RuntimeException('my-xc')],
            [true, InvalidArgumentException::class, 'my-i-xc', new InvalidArgumentException('my-i-xc')],
            [true, RuntimeException::class, 'Error during the move operation: my-i-xc', new Exception('my-i-xc')],
            [false, RuntimeException::class, 'File must be uploaded via HTTP POST: uploaded-file-name', new Exception],
        ];
    }
}