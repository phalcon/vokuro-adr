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

namespace Vokuro\Action\Session;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Contracts\Csrf;
use Vokuro\Domain\Session\ForgotPassword;
use Vokuro\Responder\AuthResponder;

/**
 * Asks for a reset link. The page it renders is the same either way: the
 * outcome only changes the message shown above the form.
 */
final class PostSessionForgotPassword implements Action
{
    public function __construct(
        private ForgotPassword $domain,
        private AuthResponder $responder,
        private Csrf $csrf
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $payload = Payload::invalid(['The form has expired, please try again']);

        if (true === $this->csrf->check($request)) {
            $payload = ($this->domain)(Input::fromRequest($request));
        }

        return ($this->responder->withTemplate('session/forgotPassword'))(
            $request,
            new Response(),
            $payload
        );
    }
}
