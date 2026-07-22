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

use Vokuro\Action\Profiles\PostProfilesEdit;
use Vokuro\Contracts\Csrf;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Profiles\UpdateProfile;
use Vokuro\Tests\Support\Fake\FakeCsrf;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostProfilesEditTest extends AbstractActionTestCase
{
    private FakeProfileRepository $profiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profiles = new FakeProfileRepository();
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: a valid submission updates and redirects
     */
    public function testUpdatesAndRedirects(): void
    {
        $this->seedProfile();

        $response = $this->action(new FakeCsrf())($this->request(['name' => 'Managers', 'active' => 'N'], [], [0 => 3]));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
        $this->assertArrayHasKey(3, $this->profiles->updated);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: a missing profile redirects to the list
     */
    public function testNotFoundRedirects(): void
    {
        $response = $this->action(new FakeCsrf())($this->request(['name' => 'Managers'], [], [0 => 999]));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: an invalid submission re-renders the form
     */
    public function testInvalidRerendersForm(): void
    {
        $this->seedProfile();

        $this->action(new FakeCsrf())($this->request(['name' => ''], [], [0 => 3]));

        $this->assertSame('profiles/edit', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: a bad CSRF token re-renders for a known profile
     */
    public function testBadCsrfRerendersForm(): void
    {
        $this->seedProfile();

        $this->action(new FakeCsrf(valid: false))($this->request([], [], [0 => 3]));

        $this->assertSame('profiles/edit', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: a bad CSRF token for a missing profile redirects
     */
    public function testBadCsrfMissingProfileRedirects(): void
    {
        $response = $this->action(new FakeCsrf(valid: false))($this->request([], [], [0 => 999]));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
    }

    private function action(Csrf $csrf): PostProfilesEdit
    {
        return new PostProfilesEdit(
            new UpdateProfile($this->profiles),
            $this->profiles,
            new FakeUserRepository(),
            $this->privateResponder(),
            $this->redirectResponder(),
            $csrf
        );
    }

    private function seedProfile(): void
    {
        $this->profiles->seed(new Profile(3, 'Auditors', true));
    }
}
