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
use Vokuro\Action\Profiles\PostProfilesCreate;
use Vokuro\Domain\Profiles\CreateProfile;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostProfilesCreateTest extends AbstractActionTestCase
{
    private FakeProfileRepository $profiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profiles = new FakeProfileRepository();
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesCreate :: a bad CSRF token re-renders the form
     */
    public function testBadCsrfRerendersForm(): void
    {
        $request  = $this->request(['csrf' => 'wrong']);
        $security = $this->security($request);

        $this->action($security)($request);

        $this->assertSame('profiles/create', $this->renderer->calls[0]['path']);
        $this->assertSame([], $this->profiles->added);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesCreate :: an invalid submission re-renders the form
     */
    public function testInvalidRerendersForm(): void
    {
        [$request, $security] = $this->signedRequest(['name' => '', 'active' => 'Y']);

        $this->action($security)($request);

        $this->assertSame('profiles/create', $this->renderer->calls[0]['path']);
        $this->assertSame([], $this->profiles->added);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\PostProfilesCreate :: a valid submission creates and redirects
     */
    public function testCreatesAndRedirects(): void
    {
        [$request, $security] = $this->signedRequest(['name' => 'Auditors', 'active' => 'Y']);

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
        $this->assertCount(1, $this->profiles->added);
    }

    private function action(Security $security): PostProfilesCreate
    {
        return new PostProfilesCreate(
            new CreateProfile($this->profiles),
            $this->privateResponder(),
            $this->redirectResponder(),
            $security
        );
    }
}
