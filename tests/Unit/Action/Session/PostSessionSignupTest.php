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
use Vokuro\Action\Session\PostSessionSignup;
use Vokuro\Domain\Session\SignUp;
use Vokuro\Tests\Support\Fake\FakeEmailConfirmationRepository;
use Vokuro\Tests\Support\Fake\FakeMailer;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostSessionSignupTest extends AbstractActionTestCase
{
    private FakeUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = new FakeUserRepository();
    }

    /**
     * Unit Tests Vokuro\Action\Session\PostSessionSignup :: a bad CSRF token re-renders the form
     */
    public function testBadCsrfRerendersForm(): void
    {
        $request  = $this->request(['csrf' => 'wrong']);
        $security = $this->security($request);

        $this->action($security)($request);

        $this->assertSame('session/signup', $this->renderer->calls[0]['path']);
        $this->assertSame([], $this->users->added);
    }

    /**
     * Unit Tests Vokuro\Action\Session\PostSessionSignup :: a valid submission registers and renders the notice
     */
    public function testValidSubmissionRegisters(): void
    {
        [$request, $security] = $this->signedRequest([
            'name'            => 'Kyle Reese',
            'email'           => 'kyle@resistance.dev',
            'password'        => 'abcdefgh',
            'confirmPassword' => 'abcdefgh',
            'terms'           => 'yes',
        ]);

        $this->action($security)($request);

        $this->assertSame('session/signup', $this->renderer->calls[0]['path']);
        $this->assertCount(1, $this->users->added);
    }

    private function action(Security $security): PostSessionSignup
    {
        $domain = new SignUp(
            $this->users,
            new FakeEmailConfirmationRepository(),
            new Security(),
            new FakeMailer()
        );

        return new PostSessionSignup($domain, $this->authResponder(), $security);
    }
}
