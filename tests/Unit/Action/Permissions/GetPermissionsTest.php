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

use Vokuro\Action\Permissions\GetPermissions;
use Vokuro\Application\Acl;
use Vokuro\Domain\Model\Profile;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetPermissionsTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Permissions\GetPermissions :: renders the profile picker with no profile chosen
     */
    public function testRendersPicker(): void
    {
        $profiles = (new FakeProfileRepository())->seed(new Profile(2, 'Users', true));

        $response = (new GetPermissions($profiles, new Acl(), $this->privateResponder()))($this->request());

        $this->assertSame('permissions/index', $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());

        $result = $this->renderer->calls[0]['params']['result'];
        $this->assertArrayHasKey('resources', $result);
        $this->assertNull($result['profile']);
    }
}
