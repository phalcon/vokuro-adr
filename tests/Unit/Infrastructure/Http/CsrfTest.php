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

use Phalcon\Encryption\Security;
use Phalcon\Http\Request;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Infrastructure\Http\Csrf;
use Vokuro\Tests\Support\Fake\FakeSession;

final class CsrfTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        $_POST = [];

        parent::tearDown();
    }

    /**
     * Unit Tests Vokuro\Infrastructure\Http\Csrf :: issues a token and accepts it back
     */
    public function testIssuesAndAcceptsTheToken(): void
    {
        $csrf = $this->csrf();

        $token = $csrf->token();
        $this->assertNotSame('', $token);

        $_POST['csrf'] = $token;
        $this->assertTrue($csrf->check(new Request()));
    }

    /**
     * Unit Tests Vokuro\Infrastructure\Http\Csrf :: rejects a wrong token
     */
    public function testRejectsAWrongToken(): void
    {
        $csrf = $this->csrf();
        $csrf->token();

        $_POST['csrf'] = 'wrong';
        $this->assertFalse($csrf->check(new Request()));
    }

    private function csrf(): Csrf
    {
        return new Csrf(new Security(new FakeSession(), new Request()));
    }
}
