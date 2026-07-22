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

namespace Vokuro\Tests\Unit\Action;

use Vokuro\Action\About\GetAbout;
use Vokuro\Action\Get;
use Vokuro\Action\Privacy\GetPrivacy;
use Vokuro\Action\Profiles\GetProfilesCreate;
use Vokuro\Action\Session\GetSessionForgotPassword;
use Vokuro\Action\Session\GetSessionLogin;
use Vokuro\Action\Session\GetSessionSignup;
use Vokuro\Action\Terms\GetTerms;
use Vokuro\Action\Users\GetUsersChangePassword;

final class RenderOnlyGetTest extends AbstractActionTestCase
{
    /**
     * Unit Tests render-only GET actions :: each renders its template with a 200
     *
     * @dataProvider actionProvider
     *
     * @param class-string $class
     */
    public function testRendersTemplate(string $class, string $responderKind, string $template): void
    {
        $responder = match ($responderKind) {
            'auth'    => $this->authResponder(),
            'private' => $this->privateResponder(),
            'view'    => $this->viewResponder(),
        };

        $response = (new $class($responder))($this->request());

        $this->assertSame($template, $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return array<string, array{0: class-string, 1: string, 2: string}>
     */
    public static function actionProvider(): array
    {
        return [
            'home'            => [Get::class, 'view', 'index/index'],
            'about'           => [GetAbout::class, 'view', 'about/index'],
            'privacy'         => [GetPrivacy::class, 'view', 'privacy/index'],
            'terms'           => [GetTerms::class, 'view', 'terms/index'],
            'login form'      => [GetSessionLogin::class, 'auth', 'session/login'],
            'signup form'     => [GetSessionSignup::class, 'auth', 'session/signup'],
            'forgot form'     => [GetSessionForgotPassword::class, 'auth', 'session/forgotPassword'],
            'profile create'  => [GetProfilesCreate::class, 'private', 'profiles/create'],
            'change password' => [GetUsersChangePassword::class, 'private', 'users/changePassword'],
        ];
    }
}
