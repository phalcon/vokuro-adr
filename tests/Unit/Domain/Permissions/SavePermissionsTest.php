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
use Vokuro\Contracts\Repository\PermissionRepository;
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Permissions\SavePermissions;

final class SavePermissionsTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Permissions\SavePermissions :: reports a missing profile
     */
    public function testProfileNotFound(): void
    {
        $profiles = $this->createMock(ProfileRepository::class);
        $profiles->method('findById')->willReturn(null);

        $permissions = $this->createMock(PermissionRepository::class);
        $permissions->expects($this->never())->method('replaceForProfile');

        $payload = (new SavePermissions($profiles, $permissions))(
            new Input(['profileId' => 99, 'permissions' => ['users.index']])
        );

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Permissions\SavePermissions :: replaces the grants with the checked set
     */
    public function testReplacesGrants(): void
    {
        $profiles = $this->createMock(ProfileRepository::class);
        $profiles->method('findById')->willReturn(new Profile(1, 'Admins', true));

        $permissions = $this->createMock(PermissionRepository::class);
        $permissions->expects($this->once())->method('replaceForProfile')
                    ->with(1, ['users.index', 'users.edit']);

        $payload = (new SavePermissions($profiles, $permissions))(
            new Input(['profileId' => 1, 'permissions' => ['users.index', 'users.edit']])
        );

        $this->assertSame(Status::UPDATED, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Permissions\SavePermissions :: clears the grants when none are checked
     */
    public function testClearsWhenNoneChecked(): void
    {
        $profiles = $this->createMock(ProfileRepository::class);
        $profiles->method('findById')->willReturn(new Profile(1, 'Admins', true));

        $permissions = $this->createMock(PermissionRepository::class);
        $permissions->expects($this->once())->method('replaceForProfile')->with(1, []);

        $payload = (new SavePermissions($profiles, $permissions))(new Input(['profileId' => 1]));

        $this->assertSame(Status::UPDATED, $payload->getStatus());
    }
}
