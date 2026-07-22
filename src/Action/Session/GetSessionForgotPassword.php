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

use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Responder\AuthResponder;

/**
 * Shows the form that asks for the address to send a reset link to.
 */
final class GetSessionForgotPassword implements Action
{
    public function __construct(
        private AuthResponder $responder
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        return ($this->responder->withTemplate('session/forgotPassword'))(
            $request,
            new Response(),
            Payload::success()
        );
    }
}
