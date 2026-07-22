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

use Vokuro\Action\Users\PostUsersEdit;
use Vokuro\Contracts\Csrf;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Users\UpdateUser;
use Vokuro\Tests\Support\Fake\FakeCsrf;
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

        $response = $this->action(new FakeCsrf())($this->request($this->fields(), [], [0 => 3]));

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

        $this->action(new FakeCsrf())($this->request(['email' => 'bad'] + $this->fields(), [], [0 => 3]));

        $this->assertSame('users/edit', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersEdit :: a missing user redirects to the list
     */
    public function testNotFoundRedirects(): void
    {
        $response = $this->action(new FakeCsrf())($this->request($this->fields(), [], [0 => 999]));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersEdit :: a bad CSRF token re-renders the form for a known user
     */
    public function testBadCsrfRerendersForm(): void
    {
        $this->seedUser();

        $this->action(new FakeCsrf(valid: false))($this->request([], [], [0 => 3]));

        $this->assertSame('users/edit', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersEdit :: a bad CSRF token for a missing user redirects
     */
    public function testBadCsrfMissingUserRedirects(): void
    {
        $response = $this->action(new FakeCsrf(valid: false))($this->request([], [], [0 => 999]));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
    }

    private function action(Csrf $csrf): PostUsersEdit
    {
        return new PostUsersEdit(
            new UpdateUser($this->users),
            $this->users,
            new FakeProfileRepository(),
            $this->privateResponder(),
            $this->redirectResponder(),
            $csrf
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
