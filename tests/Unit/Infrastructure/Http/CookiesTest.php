<?php

/**
 * This file is part of the Vökuró.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vokuro\Tests\Unit\Infrastructure\Http;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Infrastructure\Http\Cookies;

/**
 * @runTestsInSeparateProcesses
 *
 * `set()` and `delete()` call `setcookie()`, which touches the SAPI header
 * layer; a fresh process per test keeps that from tripping "headers already
 * sent" once PHPUnit has written to the output stream.
 */
final class CookiesTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        $_COOKIE = [];

        parent::tearDown();
    }

    /**
     * Unit Tests Vokuro\Infrastructure\Http\Cookies :: reads a missing cookie as null
     */
    public function testGetReturnsNullWhenAbsent(): void
    {
        $this->assertNull((new Cookies())->get('missing'));
    }

    /**
     * Unit Tests Vokuro\Infrastructure\Http\Cookies :: reads a present cookie from the jar
     */
    public function testGetReadsTheJar(): void
    {
        $_COOKIE['token'] = 'abc';

        $this->assertSame('abc', (new Cookies())->get('token'));
    }

    /**
     * Unit Tests Vokuro\Infrastructure\Http\Cookies :: set writes the value to the jar
     */
    public function testSetWritesTheJar(): void
    {
        (new Cookies())->set('token', 'abc', 123);

        $this->assertSame('abc', $_COOKIE['token']);
    }

    /**
     * Unit Tests Vokuro\Infrastructure\Http\Cookies :: delete clears the value from the jar
     */
    public function testDeleteClearsTheJar(): void
    {
        $_COOKIE['token'] = 'abc';

        (new Cookies())->delete('token');

        $this->assertArrayNotHasKey('token', $_COOKIE);
    }
}
