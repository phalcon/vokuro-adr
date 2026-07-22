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

use Phalcon\Encryption\Security;
use Vokuro\Action\Users\PostUsersEdit;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Users\UpdateUser;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostUsersEditTest extends AbstractActionTestCase
{
    private FakeUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = new FakeUserRepository();
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersEdit :: a valid submission updates and redirects
     */
    public function testUpdatesAndRedirects(): void
    {
        $this->seedUser();
        [$request, $security] = $this->signedRequest($this->fields());
        $request->getAttributes()->set(0, 3);

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
        $this->assertArrayHasKey(3, $this->users->updated);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersEdit :: an invalid submission re-renders the form
     */
    public function testInvalidRerendersForm(): void
    {
        $this->seedUser();
        [$request, $security] = $this->signedRequest(['email' => 'bad'] + $this->fields());
        $request->getAttributes()->set(0, 3);

        $this->action($security)($request);

        $this->assertSame('users/edit', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersEdit :: a missing user redirects to the list
     */
    public function testNotFoundRedirects(): void
    {
        [$request, $security] = $this->signedRequest($this->fields());
        $request->getAttributes()->set(0, 999);

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersEdit :: a bad CSRF token re-renders the form for a known user
     */
    public function testBadCsrfRerendersForm(): void
    {
        $this->seedUser();
        $request  = $this->request(['csrf' => 'wrong'], [], [0 => 3]);
        $security = $this->security($request);

        $this->action($security)($request);

        $this->assertSame('users/edit', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersEdit :: a bad CSRF token for a missing user redirects
     */
    public function testBadCsrfMissingUserRedirects(): void
    {
        $request  = $this->request(['csrf' => 'wrong'], [], [0 => 999]);
        $security = $this->security($request);

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
    }

    private function action(Security $security): PostUsersEdit
    {
        return new PostUsersEdit(
            new UpdateUser($this->users),
            $this->users,
            new FakeProfileRepository(),
            $this->privateResponder(),
            $this->redirectResponder(),
            $security
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function fields(): array
    {
        return ['name' => 'Sarah', 'email' => 's@x.dev', 'profilesId' => 2];
    }

    private function seedUser(): void
    {
        $this->users->seed(new User(3, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, false));
    }
}
