<?php declare(strict_types=1);

namespace Http\Factory;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

interface StreamFactoryInterface extends \Psr\Http\Message\StreamFactoryInterface
{
    /**
     * Open for reading only;
     * fails if file doesn't exist
     * place the file pointer at the beginning of the file.
     */
    public const READ_EXISTING = 'r';

    /**
     * Open for reading and writing;
     * fails if file doesn't exist
     * place the file pointer at the beginning of the file.
     */
    public const READ_WRITE_EXISTING = 'r+';

    /**
     * Open for writing only;
     * place the file pointer at the beginning of the file and truncate the file to zero length.
     * If the file does not exist, attempt to create it.
     */
    public const WRITE_TRUNCATE = 'w';

    /**
     * Open for reading and writing;
     * otherwise it has the same behavior as 'w'.
     */
    public const READ_WRITE_TRUNCATE = 'w+';

    /**
     * Open for writing only;
     * place the file pointer at the end of the file.
     * If the file does not exist, attempt to create it.
     * In this mode, fseek() has no effect, writes are always appended.
     */
    public const WRITE_APPEND_ONLY = 'a';

    /**
     * Open for reading and writing;
     * place the file pointer at the end of the file.
     * If the file does not exist, attempt to create it.
     * In this mode, fseek() only affects the reading position, writes are always appended.
     */
    public const READ_WRITE_APPEND_ONLY = 'a+';

    /**
     * Create and open for writing only;
     * place the file pointer at the beginning of the file.
     * If the file already exists, the fopen() call will fail by returning false and generating an error of level E_WARNING.
     * If the file does not exist, attempt to create it.
     * This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
     */
    public const WRITE_NOT_EXISTING = 'x';

    /**
     * Create and open for reading and writing; otherwise it has the same behavior as 'x'.
     */
    public const READ_WRITE_NOT_EXISTING = 'x+';

    /**
     * Open the file for writing only.
     * If the file does not exist, it is created.
     * If it exists, it is neither truncated (as opposed to 'w'), nor the call to this function fails (as is the case with 'x').
     * The file pointer is positioned on the beginning of the file.
     * This may be useful if it's desired to get an advisory lock (see flock()) before attempting to modify the file,
     * as using 'w' could truncate the file before the lock was obtained (if truncation is desired, ftruncate() can be used after the lock is requested).
     */
    public const OVERWRITE = 'c';

    /**
     * Open the file for reading and writing; otherwise it has the same behavior as 'c'.
     */
    public const OVERWRITE_WITH_READ = 'c+';

    /**
     * Set close-on-exec flag on the opened file descriptor.
     * Only available in PHP compiled on POSIX.1-2008 conform systems.
     */
    public const CLOSE_ON_EXEC = 'e';

    public const READABLE_MODES = '/r|r\+|w\+|a\+|x\+|c\+|e/';
    public const WRITABLE_MODES = '/r\+|w|w\+|a|a\+|x|x\+|c|c\+|e/';

    public const INPUT = 'php://input';
    public const OUTPUT = 'php://output';

    public const TEMP = 'php://temp';
    public const MEMORY = 'php://memory';

    public const STDIN = 'php://stdin';
    public const STDOUT = 'php://stdout';
    public const STDERR = 'php://stderr';

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
    public function createStream(string $content = '', array $metadata = []): StreamInterface;

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
    public function createStreamFromFile(string $filename, string $mode = self::READ_EXISTING, array $metadata = []): StreamInterface;

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param resource $resource PHP resource to use as basis of stream.
     * @param array $metadata metadata
     *
     * @return StreamInterface
     */
    public function createStreamFromResource($resource, array $metadata = []): StreamInterface;

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
    public function createResource(string $filename, string $mode = self::READ_EXISTING);

    /**
     * A read-only stream that allows you to read raw data from the request body.
     * @uses php://input
     */
    public function createRequestBodyStream(array $metadata = []): StreamInterface;

    /**
     * A write-only stream that allows you to write to the output buffer mechanism in the same way as print and echo.
     * @uses php://output
     */
    public function createOutputBufferStream(array $metadata = []): StreamInterface;

    /**
     * Create access to PHP's own in-memory temporary stream.
     * @uses php://memory
     */
    public function createInMemoryStream(array $metadata = []): StreamInterface;

    /**
     * Create access to PHP's own disk-backed temporary file stream.
     * @uses php://temp
     */
    public function createTempFileStream(array $metadata = []): StreamInterface;

    /**
     * Create stream access to PHP's own standard input file descriptor.
     * @uses php://stdin
     */
    public function createSTDINStream(array $metadata = []): StreamInterface;

    /**
     * Create stream access to PHP's own standard output file descriptor.
     * @uses php://stdout
     */
    public function createSTDOUTStream(array $metadata = []): StreamInterface;

    /**
     * Create stream access to PHP's own standard error file descriptor.
     * @uses php://stderr
     */
    public function createSTDERRStream(array $metadata = []): StreamInterface;
}
