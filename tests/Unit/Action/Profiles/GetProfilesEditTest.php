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

use Vokuro\Action\Profiles\GetProfilesEdit;
use Vokuro\Domain\Model\Profile;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetProfilesEditTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Profiles\GetProfilesEdit :: renders the edit form for an existing profile
     */
    public function testRendersEditForm(): void
    {
        $profiles = (new FakeProfileRepository())->seed(new Profile(3, 'Auditors', true));

        $response = $this->action($profiles)($this->request(attributes: [0 => 3]));

        $this->assertSame('profiles/edit', $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());

        $result = $this->renderer->calls[0]['params']['result'];
        $this->assertArrayHasKey('profile', $result);
        $this->assertArrayHasKey('users', $result);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\GetProfilesEdit :: a missing profile returns to the list
     */
    public function testMissingProfileRedirects(): void
    {
        $response = $this->action(new FakeProfileRepository())($this->request(attributes: [0 => 999]));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
    }

    private function action(FakeProfileRepository $profiles): GetProfilesEdit
    {
        return new GetProfilesEdit(
            $profiles,
            new FakeUserRepository(),
            $this->privateResponder(),
            $this->redirectResponder()
        );
    }
}
