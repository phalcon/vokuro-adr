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
use Vokuro\Action\Users\PostUsersCreate;
use Vokuro\Domain\Users\CreateUser;
use Vokuro\Tests\Support\Fake\FakeEmailConfirmationRepository;
use Vokuro\Tests\Support\Fake\FakeMailer;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostUsersCreateTest extends AbstractActionTestCase
{
    private FakeUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = new FakeUserRepository();
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersCreate :: a bad CSRF token re-renders the form
     */
    public function testBadCsrfRerendersForm(): void
    {
        $request  = $this->request(['csrf' => 'wrong']);
        $security = $this->security($request);

        $this->action($security)($request);

        $this->assertSame('users/create', $this->renderer->calls[0]['path']);
        $this->assertSame([], $this->users->added);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersCreate :: an invalid submission re-renders the form
     */
    public function testInvalidRerendersForm(): void
    {
        [$request, $security] = $this->signedRequest(['name' => '', 'email' => 'kate@x.dev', 'profilesId' => 2]);

        $this->action($security)($request);

        $this->assertSame('users/create', $this->renderer->calls[0]['path']);
        $this->assertSame([], $this->users->added);
    }

    /**
     * Unit Tests Vokuro\Action\Users\PostUsersCreate :: a valid submission creates and redirects
     */
    public function testCreatesAndRedirects(): void
    {
        [$request, $security] = $this->signedRequest(['name' => 'Kate', 'email' => 'kate@x.dev', 'profilesId' => 2]);

        $response = $this->action($security)($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
        $this->assertCount(1, $this->users->added);
    }

    private function action(Security $security): PostUsersCreate
    {
        $domain = new CreateUser(
            $this->users,
            new FakeEmailConfirmationRepository(),
            new Security(),
            new FakeMailer()
        );

        return new PostUsersCreate(
            $domain,
            new FakeProfileRepository(),
            $this->privateResponder(),
            $this->redirectResponder(),
            $security
        );
    }
}
