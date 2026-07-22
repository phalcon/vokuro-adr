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

namespace Vokuro\Tests\Unit\Action\Permissions;

use Phalcon\Encryption\Security;
use Vokuro\Action\Permissions\PostPermissions;
use Vokuro\Application\Acl;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Permissions\SavePermissions;
use Vokuro\Tests\Support\Fake\FakePermissionRepository;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostPermissionsTest extends AbstractActionTestCase
{
    private FakePermissionRepository $permissions;

    private FakeProfileRepository $profiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profiles    = new FakeProfileRepository();
        $this->permissions = new FakePermissionRepository();
    }

    /**
     * Unit Tests Vokuro\Action\Permissions\PostPermissions :: a bad CSRF token re-renders with a message
     */
    public function testBadCsrfRerendersWithMessage(): void
    {
        $request  = $this->request(['csrf' => 'wrong']);
        $security = $this->security($request);

        $this->action($security)($request);

        $this->assertSame('permissions/index', $this->renderer->calls[0]['path']);
        $this->assertContains(
            'The form has expired, please try again',
            $this->renderer->calls[0]['params']['messages']
        );
    }

    /**
     * Unit Tests Vokuro\Action\Permissions\PostPermissions :: an unknown profile re-renders the picker
     */
    public function testUnknownProfileRerenders(): void
    {
        [$request, $security] = $this->signedRequest(['profileId' => 999]);

        $this->action($security)($request);

        $this->assertSame('permissions/index', $this->renderer->calls[0]['path']);
        $this->assertNull($this->renderer->calls[0]['params']['result']['profile']);
    }

    /**
     * Unit Tests Vokuro\Action\Permissions\PostPermissions :: choosing a profile reveals its grants
     */
    public function testShowsGrants(): void
    {
        $this->profiles->seed(new Profile(2, 'Users', true));
        [$request, $security] = $this->signedRequest(['profileId' => 2]);

        $this->action($security)($request);

        $this->assertSame(2, $this->renderer->calls[0]['params']['result']['profile']);
        $this->assertSame([], $this->permissions->replaced);
    }

    /**
     * Unit Tests Vokuro\Action\Permissions\PostPermissions :: submitting replaces the grants
     */
    public function testSavesGrants(): void
    {
        $this->profiles->seed(new Profile(2, 'Users', true));
        [$request, $security] = $this->signedRequest([
            'profileId'   => 2,
            'submit'      => '1',
            'permissions' => ['users.index'],
        ]);

        $this->action($security)($request);

        $this->assertSame('permissions/index', $this->renderer->calls[0]['path']);
        $this->assertCount(1, $this->permissions->replaced);
    }

    private function action(Security $security): PostPermissions
    {
        return new PostPermissions(
            new SavePermissions($this->profiles, $this->permissions),
            $this->profiles,
            $this->permissions,
            new Acl(),
            $this->privateResponder(),
            $security
        );
    }
}
