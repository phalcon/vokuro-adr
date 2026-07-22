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

namespace Vokuro\Tests\Unit\Action\Users;

use Vokuro\Action\Users\GetUsers;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetUsersTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Users\GetUsers :: renders the paged list with the profile filter
     */
    public function testRendersPagedList(): void
    {
        $action = new GetUsers(
            new FakeUserRepository(),
            new FakeProfileRepository(),
            $this->privateResponder()
        );

        $response = $action($this->request(query: ['page' => '1']));

        $this->assertSame('users/index', $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());

        $result = $this->renderer->calls[0]['params']['result'];
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('profiles', $result);
    }
}
