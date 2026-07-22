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

use Phalcon\Encryption\Security;
use Vokuro\Action\Profiles\PostProfilesEdit;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Profiles\UpdateProfile;
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
        [$request, $security] = $this->signedRequest(['name' => 'Managers', 'active' => 'N']);
        $request->getAttributes()->set(0, 3);

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
        $this->assertArrayHasKey(3, $this->profiles->updated);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: a missing profile redirects to the list
     */
    public function testNotFoundRedirects(): void
    {
        [$request, $security] = $this->signedRequest(['name' => 'Managers']);
        $request->getAttributes()->set(0, 999);

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: an invalid submission re-renders the form
     */
    public function testInvalidRerendersForm(): void
    {
        $this->seedProfile();
        [$request, $security] = $this->signedRequest(['name' => '']);
        $request->getAttributes()->set(0, 3);

        $this->action($security)($request);

        $this->assertSame('profiles/edit', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: a bad CSRF token re-renders for a known profile
     */
    public function testBadCsrfRerendersForm(): void
    {
        $this->seedProfile();
        $request  = $this->request(['csrf' => 'wrong'], [], [0 => 3]);
        $security = $this->security($request);

        $this->action($security)($request);

        $this->assertSame('profiles/edit', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesEdit :: a bad CSRF token for a missing profile redirects
     */
    public function testBadCsrfMissingProfileRedirects(): void
    {
        $request  = $this->request(['csrf' => 'wrong'], [], [0 => 999]);
        $security = $this->security($request);

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
    }

    private function action(Security $security): PostProfilesEdit
    {
        return new PostProfilesEdit(
            new UpdateProfile($this->profiles),
            $this->profiles,
            new FakeUserRepository(),
            $this->privateResponder(),
            $this->redirectResponder(),
            $security
        );
    }

    private function seedProfile(): void
    {
        $this->profiles->seed(new Profile(3, 'Auditors', true));
    }
}
