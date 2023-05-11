<?php declare(strict_types=1);

namespace Http\Message;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\UriInterface;

/**
 * @see https://datatracker.ietf.org/doc/html/rfc3986#section-3
 */
class Uri implements UriInterface
{
    public const SCHEME_SUFFIX = ':';
    public const HOST_PREFIX = '//';
    public const PASS_PREFIX = ':';
    public const USERINFO_SUFFIX = '@';
    public const PORT_PREFIX = ':';
    public const QUERY_PREFIX = '?';
    public const FRAGMENT_PREFIX = '#';

    #[Pure] public function __construct(
        private string   $scheme = '',
        private string   $user = '',
        private string   $pass = '',
        private string   $host = '',
        private null|int $port = null,
        private string   $path = '',
        private string   $query = '',
        private string   $fragment = ''
    )
    {
        $this->scheme = $this->withScheme($scheme)->scheme;
        [$this->user, $this->pass] = $this->parseUserInfo($user, $pass);
        $this->host = $this->withHost($host)->host;
        $this->port = $this->withPort($port)->port;
        $this->path = $this->withPath($path)->path;
        $this->query = $this->withQuery($query)->query;
        $this->fragment = $this->withFragment($fragment)->fragment;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    #[Pure] public function getAuthority(): string
    {
        return $this->useSuffix($this->getUserInfo(), self::USERINFO_SUFFIX)
            . $this->host
            . $this->usePrefix(strval($this->port), self::PORT_PREFIX);
    }

    #[Pure] public function getUserInfo(): string
    {
        return $this->user . $this->usePrefix($this->pass, self::PASS_PREFIX);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    #[Pure] public function withScheme(string $scheme): UriInterface
    {
        $scheme = $this->parseUrl(rtrim($scheme, ':') . '://host', PHP_URL_SCHEME, true);

        if ($scheme === $this->scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    #[Pure] public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        [$user, $pass] = $this->parseUserInfo($user, $password);

        if ($user === $this->user && $pass === $this->pass) {
            return $this;
        }

        $new = clone $this;
        $new->user = $user;
        $new->pass = $pass;
        return $new;
    }

    #[Pure] public function withHost(string $host): UriInterface
    {
        $host = $this->parseUrl('scheme://' . $host, PHP_URL_HOST, true);

        if ($host === $this->host) {
            return $this;
        }

        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function withPort(?int $port): UriInterface
    {
        if ($port === $this->port) {
            return $this;
        }

        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    #[Pure] public function withPath(string $path): UriInterface
    {
        $path = $this->parseUrl('scheme://host/' . ltrim($path, '/'), PHP_URL_PATH);

        if ($path === $this->path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    #[Pure] public function withQuery(string $query): UriInterface
    {
        $query = $this->parseUrl('scheme://host?' . ltrim($query, '?'), PHP_URL_QUERY);

        if ($query === $this->query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    #[Pure] public function withFragment(string $fragment): UriInterface
    {
        $fragment = $this->parseUrl('scheme://host#' . ltrim($fragment, '#'), PHP_URL_FRAGMENT);

        if ($fragment === $this->fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    #[Pure] public function __toString(): string
    {
        $uri = (trim($this->path, '/') ? '/' . trim($this->path, '/') : '')
            . $this->usePrefix($this->query, self::QUERY_PREFIX)
            . $this->usePrefix($this->fragment, self::FRAGMENT_PREFIX);

        if ($this->host) {
            $uri = $this->useSuffix($this->scheme, self::SCHEME_SUFFIX)
                . $this->usePrefix($this->getAuthority(), self::HOST_PREFIX)
                . $uri;
        }

        return $uri ?: $this->path;
    }

    private function useSuffix(string $value, string $suffix = ''): string
    {
        return strlen($value) ? $value . $suffix : '';
    }

    private function usePrefix(string $value, string $prefix): string
    {
        return strlen($value) ? $prefix . $value : '';
    }

    private function parseUrl(string $url, int $component, bool $lowercase = false): string
    {
        $component = (($part = parse_url($url, $component)) === false) ? '' : $part ?? '';
        return $lowercase ? strtolower($component) : $component;
    }

    /**
     * @return array<string,string>
     */
    #[Pure] private function parseUserInfo(string $user, null|string $password): array
    {
        return [
            $this->parseUrl('scheme://' . $user . ':' . $password . '@host', PHP_URL_USER),
            $this->parseUrl('scheme://' . $user . ':' . $password . '@host', PHP_URL_PASS)
        ];
    }
}
