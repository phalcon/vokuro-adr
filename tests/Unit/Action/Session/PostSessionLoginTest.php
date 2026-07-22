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

namespace Vokuro\Tests\Unit\Action\Session;

use Phalcon\Encryption\Security;
use Vokuro\Action\Session\PostSessionLogin;
use Vokuro\Application\RememberMe;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Session\Login;
use Vokuro\Tests\Support\Fake\FakeCookies;
use Vokuro\Tests\Support\Fake\FakeFailedLoginRepository;
use Vokuro\Tests\Support\Fake\FakeRememberTokenRepository;
use Vokuro\Tests\Support\Fake\FakeSuccessLoginRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostSessionLoginTest extends AbstractActionTestCase
{
    private FakeFailedLoginRepository $failed;

    private FakeRememberTokenRepository $tokens;

    private FakeUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users  = new FakeUserRepository();
        $this->failed = new FakeFailedLoginRepository();
        $this->tokens = new FakeRememberTokenRepository();
    }

    /**
     * Unit Tests Vokuro\Action\Session\PostSessionLogin :: a bad CSRF token re-renders the form
     */
    public function testBadCsrfRerendersForm(): void
    {
        $request  = $this->request(['csrf' => 'wrong'], server: $this->clientServer());
        $security = $this->security($request);

        $response = $this->action($security)($request);

        $this->assertSame('session/login', $this->renderer->calls[0]['path']);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([], $this->failed->added);
    }

    /**
     * Unit Tests Vokuro\Action\Session\PostSessionLogin :: wrong credentials re-render and record a failure
     */
    public function testWrongCredentialsRerenderAndRecord(): void
    {
        [$request, $security] = $this->signedRequest(
            ['email' => 'sarah@x.dev', 'password' => 'wrong'],
            $this->clientServer()
        );

        $response = $this->action($security)($request);

        $this->assertSame('session/login', $this->renderer->calls[0]['path']);
        $this->assertNotSame(302, $response->getStatusCode());
        $this->assertCount(1, $this->failed->added);
    }

    /**
     * Unit Tests Vokuro\Action\Session\PostSessionLogin :: a valid sign in redirects and stores the identity
     */
    public function testSuccessRedirectsAndSetsSession(): void
    {
        $this->seedUser('secret');

        [$request, $security] = $this->signedRequest(
            ['email' => 'sarah@x.dev', 'password' => 'secret'],
            $this->clientServer()
        );

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/', $response->getHeaders()->get('Location'));
        $this->assertSame(7, $this->session->get('auth')['id']);
        $this->assertSame([], $this->tokens->added);
    }

    /**
     * Unit Tests Vokuro\Action\Session\PostSessionLogin :: remembering the sign in stores a token
     */
    public function testRememberStoresToken(): void
    {
        $this->seedUser('secret');

        [$request, $security] = $this->signedRequest(
            ['email' => 'sarah@x.dev', 'password' => 'secret', 'remember' => 'yes'],
            $this->clientServer()
        );

        $this->action($security)($request);

        $this->assertCount(1, $this->tokens->added);
    }

    private function action(Security $security): PostSessionLogin
    {
        $domain = new Login(
            $this->users,
            new FakeSuccessLoginRepository(),
            $this->failed,
            new Security()
        );

        $rememberMe = new RememberMe($this->tokens, $this->users, new FakeCookies());

        return new PostSessionLogin(
            $domain,
            $this->authResponder(),
            $this->redirectResponder(),
            $this->session,
            $security,
            $rememberMe
        );
    }

    /**
     * @return array<string, string>
     */
    private function clientServer(): array
    {
        return ['REMOTE_ADDR' => '1.2.3.4', 'HTTP_USER_AGENT' => 'agent'];
    }

    private function seedUser(string $password): void
    {
        $this->users->seed(new User(
            id: 7,
            name: 'Sarah',
            email: 'sarah@x.dev',
            passwordHash: (new Security())->hash($password),
            profileId: 2,
            profileName: 'Users',
            active: true,
            banned: false,
            suspended: false,
            mustChangePassword: false
        ));
    }
}
