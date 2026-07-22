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

namespace Vokuro\Tests\Unit\Domain\Permissions;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Status;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Permissions\SavePermissions;
use Vokuro\Tests\Support\Fake\FakePermissionRepository;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;

final class SavePermissionsTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Permissions\SavePermissions :: reports a missing profile
     */
    public function testProfileNotFound(): void
    {
        $permissions = new FakePermissionRepository();

        $payload = (new SavePermissions(new FakeProfileRepository(), $permissions))(
            new Input(['profileId' => 99, 'permissions' => ['users.index']])
        );

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
        $this->assertSame([], $permissions->replaced);
    }

    /**
     * Unit Tests Vokuro\Domain\Permissions\SavePermissions :: replaces the grants with the checked set
     */
    public function testReplacesGrants(): void
    {
        $profiles = new FakeProfileRepository();
        $profiles->seed(new Profile(1, 'Admins', true));

        $permissions = new FakePermissionRepository();

        $payload = (new SavePermissions($profiles, $permissions))(
            new Input(['profileId' => 1, 'permissions' => ['users.index', 'users.edit']])
        );

        $this->assertSame(Status::UPDATED, $payload->getStatus());
        $this->assertSame(['profileId' => 1, 'pairs' => ['users.index', 'users.edit']], $permissions->replaced[0]);
    }

    /**
     * Unit Tests Vokuro\Domain\Permissions\SavePermissions :: clears the grants when none are checked
     */
    public function testClearsWhenNoneChecked(): void
    {
        $profiles = new FakeProfileRepository();
        $profiles->seed(new Profile(1, 'Admins', true));

        $permissions = new FakePermissionRepository();

        $payload = (new SavePermissions($profiles, $permissions))(new Input(['profileId' => 1]));

        $this->assertSame(Status::UPDATED, $payload->getStatus());
        $this->assertSame(['profileId' => 1, 'pairs' => []], $permissions->replaced[0]);
    }
}
