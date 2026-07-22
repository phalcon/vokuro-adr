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

namespace Vokuro\Tests\Unit\Action\Profiles;

use Vokuro\Action\Profiles\GetProfiles;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetProfilesTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Profiles\GetProfiles :: renders the paged list
     */
    public function testRendersPagedList(): void
    {
        $response = (new GetProfiles(new FakeProfileRepository(), $this->privateResponder()))(
            $this->request(query: ['page' => '1'])
        );

        $this->assertSame('profiles/index', $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('page', $this->renderer->calls[0]['params']['result']);
    }
}
