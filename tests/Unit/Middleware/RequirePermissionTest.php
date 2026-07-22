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
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Middleware\RequirePermission;
use Vokuro\Tests\Support\Fake\FakeAuthorization;
use Vokuro\Tests\Support\Fake\FakeHandler;
use Vokuro\Tests\Support\Fake\FakeSession;

final class RequirePermissionTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_URI']);

        parent::tearDown();
    }

    /**
     * Unit Tests Vokuro\Middleware\RequirePermission :: maps the path to a permission and allows
     */
    public function testAllowsWhenGranted(): void
    {
        $authorization = (new FakeAuthorization())->allow(2, 'users', 'delete');
        $next          = new FakeHandler();

        $this->middleware($authorization)($this->request('/users/delete/3'), $next);

        $this->assertTrue($next->called);
        $this->assertSame(['profileId' => 2, 'resource' => 'users', 'action' => 'delete'], $authorization->asked[0]);
    }

    /**
     * Unit Tests Vokuro\Middleware\RequirePermission :: sends a denied profile home
     */
    public function testDeniesWhenNotGranted(): void
    {
        $next = new FakeHandler();

        $response = $this->middleware(new FakeAuthorization())($this->request('/permissions'), $next);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/', $response->getHeaders()->get('Location'));
        $this->assertFalse($next->called);
    }

    /**
     * Unit Tests Vokuro\Middleware\RequirePermission :: a bare resource is the index action
     */
    public function testBareResourceMapsToIndex(): void
    {
        $authorization = (new FakeAuthorization())->allow(2, 'users', 'index');
        $next          = new FakeHandler();

        $this->middleware($authorization)($this->request('/users'), $next);

        $this->assertTrue($next->called);
        $this->assertSame(['profileId' => 2, 'resource' => 'users', 'action' => 'index'], $authorization->asked[0]);
    }

    private function middleware(FakeAuthorization $authorization): RequirePermission
    {
        return new RequirePermission(new FakeSession(['auth' => ['profilesId' => 2]]), $authorization);
    }

    private function request(string $path): Request
    {
        $_SERVER['REQUEST_URI'] = $path;

        return new Request();
    }
}
