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
use Vokuro\Action\Users\PostUsersChangePassword;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Users\ChangePassword;
use Vokuro\Tests\Support\Fake\FakePasswordChangeRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostUsersChangePasswordTest extends AbstractActionTestCase
{
    private FakeUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = new FakeUserRepository();
        $this->session->set('auth', ['id' => 7]);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersChangePassword :: a bad CSRF token re-renders the form
     */
    public function testBadCsrfRerendersForm(): void
    {
        $request  = $this->request(['csrf' => 'wrong'], server: $this->clientServer());
        $security = $this->security($request);

        $this->action($security)($request);

        $this->assertSame('users/changePassword', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersChangePassword :: an invalid submission re-renders the form
     */
    public function testInvalidRerendersForm(): void
    {
        $this->users->seed($this->user());
        [$request, $security] = $this->signedRequest(
            ['password' => 'short', 'confirmPassword' => 'short'],
            $this->clientServer()
        );

        $this->action($security)($request);

        $this->assertSame('users/changePassword', $this->renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersChangePassword :: a valid change updates and redirects
     */
    public function testChangesAndRedirects(): void
    {
        $this->users->seed($this->user());
        [$request, $security] = $this->signedRequest(
            ['password' => 'newpassword1', 'confirmPassword' => 'newpassword1'],
            $this->clientServer()
        );

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
        $this->assertArrayHasKey(7, $this->users->updated);
    }

    private function action(Security $security): PostUsersChangePassword
    {
        $domain = new ChangePassword($this->users, new FakePasswordChangeRepository(), new Security());

        return new PostUsersChangePassword(
            $domain,
            $this->privateResponder(),
            $this->redirectResponder(),
            $security,
            $this->session
        );
    }

    /**
     * @return array<string, string>
     */
    private function clientServer(): array
    {
        return ['REMOTE_ADDR' => '1.2.3.4', 'HTTP_USER_AGENT' => 'agent'];
    }

    private function user(): User
    {
        return new User(7, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, true);
    }
}
