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

use Vokuro\Action\Profiles\GetProfilesSearch;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetProfilesSearchTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Profiles\GetProfilesSearch :: renders the filtered results
     */
    public function testRendersResults(): void
    {
        $response = (new GetProfilesSearch(new FakeProfileRepository(), $this->privateResponder()))(
            $this->request(query: ['name' => 'Admins'])
        );

        $this->assertSame('profiles/search', $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());

        $result = $this->renderer->calls[0]['params']['result'];
        $this->assertArrayHasKey('page', $result);
        $this->assertSame('name=Admins', $result['query']);
    }
}
