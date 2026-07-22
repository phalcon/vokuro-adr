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

namespace Vokuro\Tests\Unit\Application;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Application\Authorizer;
use Vokuro\Contracts\Repository\PermissionRepository;

final class AuthorizerTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Application\Authorizer :: grants a held permission and denies the rest
     */
    public function testGrantsAndDenies(): void
    {
        $permissions = $this->createMock(PermissionRepository::class);
        $permissions->method('grantedTo')->willReturn(['users.index' => true]);

        $authorizer = new Authorizer($permissions);

        $this->assertTrue($authorizer->isAllowed(1, 'users', 'index'));
        $this->assertFalse($authorizer->isAllowed(1, 'users', 'delete'));
    }

    /**
     * Unit Tests Vokuro\Application\Authorizer :: reads a profile's grants once per request
     */
    public function testMemoizesGrantsPerProfile(): void
    {
        $permissions = $this->createMock(PermissionRepository::class);
        $permissions->expects($this->once())
                    ->method('grantedTo')
                    ->with(1)
                    ->willReturn(['users.index' => true]);

        $authorizer = new Authorizer($permissions);

        $authorizer->isAllowed(1, 'users', 'index');
        $authorizer->isAllowed(1, 'users', 'delete');
        $authorizer->isAllowed(1, 'profiles', 'index');
    }
}
