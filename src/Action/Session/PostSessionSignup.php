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
use Vokuro\Domain\Session\SignUp;
use Vokuro\Responder\AuthResponder;

/**
 * Registers an account. The page is rendered again either way: with the
 * per-field errors, or with the confirmation notice.
 */
final class PostSessionSignup implements Action
{
    public function __construct(
        private SignUp $domain,
        private AuthResponder $responder,
        private Csrf $csrf
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $payload = Payload::invalid(
            ['csrf' => 'The form has expired, please try again']
        );

        if (true === $this->csrf->check($request)) {
            $payload = ($this->domain)(Input::fromRequest($request));
        }

        return ($this->responder->withTemplate('session/signup'))(
            $request,
            new Response(),
            $payload
        );
    }
}
