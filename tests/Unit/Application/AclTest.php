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
use Vokuro\Application\Acl;

final class AclTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Application\Acl :: lists the resources and their actions
     */
    public function testResources(): void
    {
        $resources = (new Acl())->resources();

        $this->assertArrayHasKey('users', $resources);
        $this->assertArrayHasKey('profiles', $resources);
        $this->assertArrayHasKey('permissions', $resources);
        $this->assertContains('delete', $resources['users']);
        $this->assertContains('index', $resources['permissions']);
    }

    /**
     * Unit Tests Vokuro\Application\Acl :: describes an action, falling back to its name
     */
    public function testActionDescription(): void
    {
        $acl = new Acl();

        $this->assertSame('Access', $acl->actionDescription('index'));
        $this->assertSame('Change password', $acl->actionDescription('changePassword'));
        $this->assertSame('unknown', $acl->actionDescription('unknown'));
    }
}
