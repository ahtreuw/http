<?php declare(strict_types=1);

namespace Http\Message;

use PHPUnit\Framework\TestCase;
use Http\Message\Uri;

class UriTest extends TestCase
{
    /**
     * @dataProvider constructProvider
     */
    public function testScheme(): void
    {
        $args = func_get_args();
        $expected = array_shift($args);

        $uri = new Uri(...$args);

        self::assertEquals($expected, (string)$uri);
    }

    public static function constructProvider(): array
    {
        return [
            ['/', '', '', '', '', null, '', '', ''],
            ['/', '', '', '', '', null, '/', '', ''],
            ['s://:p@h', 's', '', 'p', 'h', null, '', '', ''],
            ['s://u:p@h', 'S', 'u', 'p', 'H', null, '', '', ''],
            ['s://u:p@h', 's', 'u', 'p', 'h', null, '', '', ''],
            ['s://u:p@h', 's', 'u', 'p', 'h', null, '', '', ''],
            ['s://u:p@h:0', 's', 'u', 'p', 'h', 0, '', '', ''],
            ['s://u:p@h:8080', 's', 'u', 'p', 'h', 8080, '', '', ''],
            ['/path?q=1#f', 's', 'u', 'p', '', null, '/path/', '?q=1', '#f'],
            ['//h?q=1#f', '', '', '', 'h', null, '/', 'q=1', 'f'],
            ['/path', '', '', '', '', null, '/path/', '', ''],
            ['#f', '', '', '', '', null, '', '', 'f'],
            ['?q=1#f', '', '', '', '', null, '/', 'q=1', 'f'],
        ];
    }

    /**
     * @dataProvider withProvider
     */
    public function testWith(string $method, $createValues, $sameValues, $newValues): void
    {
        $uri = new Uri(...$createValues);

        $new = $uri->$method(...$sameValues);
        self::assertSame($uri, $new);

        $new = $uri->$method(...$newValues);
        self::assertNotSame($uri, $new);
    }

    public static function withProvider(): array
    {
        return [
            'withScheme' => ['withScheme', ['scheme' => 'http'], ['http'], ['https']],
            'withUserInfo1' => ['withUserInfo', ['user' => 'user1'], ['user1'], ['user2']],
            'withUserInfo2' => ['withUserInfo', ['user' => 'user1', 'pass' => 'pass1'], ['user' => 'user1', 'password' => 'pass1'], ['user' => 'user1', 'password' => 'pass2']],
            'withHost' => ['withHost', ['host' => 'my-domain'], ['my-domain'], ['your-domain']],
            'withPort1' => ['withPort', ['port' => null], [null], [80]],
            'withPort2' => ['withPort', ['port' => 8080], [8080], [80]],
            'withPort3' => ['withPort', ['port' => 80], [80], [null]],
            'withPath' => ['withPath', ['path' => '/my-path'], ['/my-path'], ['/your-path']],
            'withQuery' => ['withQuery', ['query' => 'my-query'], ['my-query'], ['your-query']],
            'withFragment' => ['withFragment', ['fragment' => 'my-fragment'], ['my-fragment'], ['your-fragment']],
        ];
    }

    /**
     * @dataProvider getterProvider
     */
    public function testGetters(string $method, $createValues, $expectedValue): void
    {
        $uri = new Uri(...$createValues);
        self::assertSame($expectedValue, $uri->$method());
    }

    public static function getterProvider(): array
    {
        return [
            'getScheme' => ['getScheme', ['scheme' => 'http'], 'http'],
            'getUserInfo1' => ['getUserInfo', ['user' => 'user1'], 'user1'],
            'getUserInfo2' => ['getUserInfo', ['user' => 'user1', 'pass' => 'pass1'], 'user1:pass1'],
            'getHost' => ['getHost', ['host' => 'my-domain'], 'my-domain'],
            'getPort1' => ['getPort', ['port' => null], null],
            'getPort2' => ['getPort', ['port' => 8080], 8080],
            'getPort3' => ['getPort', ['port' => 80], 80],
            'getPath' => ['getPath', ['path' => '/my-path'], '/my-path'],
            'getQuery' => ['getQuery', ['query' => 'my-query'], 'my-query'],
            'getFragment' => ['getFragment', ['fragment' => 'my-fragment'], 'my-fragment'],
        ];
    }
}