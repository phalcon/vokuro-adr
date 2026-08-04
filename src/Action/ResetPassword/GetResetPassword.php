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

namespace Vokuro\Action\ResetPassword;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Payload\Status;
use Phalcon\ADR\Responder\Redirect;
use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Session\ManagerInterface;
use Vokuro\Domain\Session\ResetPassword;

/**
 * Handles the reset link from the e-mail, `/reset-password/{code}/{email}`.
 *
 * A live code signs the user in and drops them on the change-password form -
 * the point of the link, and the reason it is not behind `RequireLogin`: it is
 * how a locked-out user gets a session in the first place. A spent code goes to
 * the login form instead, so following the same link twice is harmless.
 */
final class GetResetPassword implements Action
{
    public function __construct(
        private ResetPassword $domain,
        private RedirectResponder $redirect,
        private ManagerInterface $session
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $code    = $request->getAttributes()->get('code', '');
        $payload = ($this->domain)(Input::fromArray(['code' => $code]));

        $to = match ($payload->getStatus()) {
            Status::UPDATED   => $this->signIn((array) $payload->getResult()),
            Status::NOT_VALID => '/session/login',
            default           => '/',
        };

        return ($this->redirect)(
            $request,
            new Response(),
            Payload::found(new Redirect($to))
        );
    }

    /**
     * Declared exactly as {@see \Vokuro\Action\Confirm\GetConfirm}: the mail
     * builds both segments, the code is alphanumeric by construction, and the
     * address is named for the route's sake - the code alone identifies the
     * request.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function params(): array
    {
        return [
            'code'  => ['match' => '[a-zA-Z0-9]+'],
            'email' => ['match' => '[^/]+@[^/]+', 'convert' => 'strtolower'],
        ];
    }

    /**
     * @param array<string, mixed> $user
     */
    private function signIn(array $user): string
    {
        $this->session->set(
            'auth',
            [
                'id'         => $user['id'],
                'name'       => $user['name'],
                'email'      => $user['email'],
                'profilesId' => $user['profilesId'],
            ]
        );

        return '/users/changePassword';
    }
}
