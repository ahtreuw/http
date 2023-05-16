<?php declare(strict_types=1);

namespace Http\Client;

use CurlHandle;

class cURLRequest implements cURLRequestInterface
{
    private false|CurlHandle $ch = false;

    /**
     * @inheritDoc
     */
    public function curl_setopt_array(array $options): bool
    {
        return curl_setopt_array($this->ch, $options);
    }

    /**
     * @inheritDoc
     */
    public function curl_setopt(int $option, mixed $value): bool
    {
        return curl_setopt($this->ch, $option, $value);
    }

    /**
     * @inheritDoc
     */
    public function curl_exec(): string|bool
    {
        return curl_exec($this->ch);
    }

    /**
     * @inheritDoc
     */
    public function curl_errno(): int
    {
        return curl_errno($this->ch);
    }

    /**
     * @inheritDoc
     */
    public function curl_error(): string
    {
        return curl_error($this->ch);
    }

    /**
     * @inheritDoc
     */
    public function curl_getinfo(): array
    {
        return curl_getinfo($this->ch);
    }

    /**
     * @inheritDoc
     */
    public function curl_init(): bool
    {
        if ($this->ch instanceof CurlHandle) {
            return true;
        }

        $this->ch = curl_init();

        return $this->ch !== false;
    }

    /**
     * @inheritDoc
     */
    public function curl_close(): void
    {
        if ($this->ch instanceof CurlHandle) {
            curl_close($this->ch);
            $this->ch = false;
        }
    }

    public function __destruct()
    {
        if ($this->ch instanceof CurlHandle) {
            $this->curl_close();
        }
    }
}
