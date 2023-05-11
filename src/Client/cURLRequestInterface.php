<?php declare(strict_types=1);

namespace Http\Client;

interface cURLRequestInterface
{
    /**
     * Set multiple options for a cURL transfer
     * @link https://php.net/manual/en/function.curl-setopt-array.php
     * @return bool true if all options were successfully set.
     */
    public function curl_setopt_array(array $options): bool;

    /**
     * Perform a cURL session
     * @link https://php.net/manual/en/function.curl-exec.php
     * @return string|bool true on success or false on failure. However, if the CURLOPT_RETURNTRANSFER
     * option is set, it will return the result on success, false on failure.
     */
    public function curl_exec(): string|bool;

    /**
     * Return the last error number
     * @link https://php.net/manual/en/function.curl-errno.php
     * @return int the error number or 0 (zero) if no error occurred.
     */
    public function curl_errno(): int;

    /**
     * Return a string containing the last error for the current session
     * @link https://php.net/manual/en/function.curl-error.php
     * @return string the error message or '' (the empty string) if no error occurred.
     */
    public function curl_error(): string;

    /**
     * Get information regarding a specific transfer
     * @link https://php.net/manual/en/function.curl-getinfo.php
     */
    public function curl_getinfo(): array;

    /**
     * Initialize a cURL session
     * @link https://php.net/manual/en/function.curl-init.php
     * @return bool true on success, false on errors.
     */
    public function curl_init(): bool;

    /**
     * Close a cURL session
     * @link https://php.net/manual/en/function.curl-close.php
     */
    public function curl_close(): void;
}
