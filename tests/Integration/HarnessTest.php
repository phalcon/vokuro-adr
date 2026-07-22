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

namespace Vokuro\Tests\Integration;

final class HarnessTest extends AbstractIntegrationTestCase
{
    /**
     * Integration Tests AbstractIntegrationTestCase :: connects, truncates and seeds
     */
    public function testConnectsCleansAndSeeds(): void
    {
        $this->clean('profiles');

        $id = $this->insert('profiles', ['name' => 'Smoke', 'active' => 'Y']);

        $this->assertSame(1, $id);

        $row = $this->connection->fetchOne(
            'SELECT id, name, active FROM profiles WHERE id = ' . $id
        );

        $this->assertSame('Smoke', $row['name']);
        $this->assertSame('Y', $row['active']);
    }
}
