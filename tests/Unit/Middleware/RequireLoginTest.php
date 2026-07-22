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

namespace Vokuro\Tests\Unit\Middleware;

use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Middleware\RequireLogin;
use Vokuro\Tests\Support\Fake\FakeHandler;
use Vokuro\Tests\Support\Fake\FakeSession;

final class RequireLoginTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Middleware\RequireLogin :: sends a signed out visitor to the login form
     */
    public function testRedirectsWhenSignedOut(): void
    {
        $next = new FakeHandler();

        $response = (new RequireLogin(new FakeSession()))(new Request(), $next);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/session/login', $response->getHeaders()->get('Location'));
        $this->assertFalse($next->called);
    }

    /**
     * Unit Tests Vokuro\Middleware\RequireLogin :: passes a signed in visitor through
     */
    public function testPassesWhenSignedIn(): void
    {
        $expected = new Response();
        $next     = new FakeHandler($expected);
        $session  = new FakeSession(['auth' => ['id' => 7]]);

        $response = (new RequireLogin($session))(new Request(), $next);

        $this->assertSame($expected, $response);
        $this->assertTrue($next->called);
    }
}
