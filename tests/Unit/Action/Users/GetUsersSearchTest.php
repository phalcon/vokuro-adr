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

use Vokuro\Action\Users\GetUsersSearch;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetUsersSearchTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Users\GetUsersSearch :: renders the filtered results
     */
    public function testRendersResults(): void
    {
        $action = new GetUsersSearch(new FakeUserRepository(), $this->privateResponder());

        $response = $action($this->request(query: ['name' => 'Sarah']));

        $this->assertSame('users/search', $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());

        $result = $this->renderer->calls[0]['params']['result'];
        $this->assertArrayHasKey('page', $result);
        $this->assertSame('name=Sarah', $result['query']);
    }
}
