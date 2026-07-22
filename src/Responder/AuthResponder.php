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

namespace Vokuro\Responder;

use Phalcon\ADR\Responder\ViewResponder;
use Phalcon\Contracts\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Responder\Responder;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;

/**
 * Renders a page in the authentication layout.
 *
 * `ViewResponder` is bound to one renderer, and one renderer knows one layout,
 * so the layout an action wants is declared by which responder it asks for.
 * This one wraps a `ViewResponder` built on the auth layout; everything else
 * behaves identically.
 */
final class AuthResponder implements Responder
{
    public function __construct(
        private ViewResponder $responder
    ) {
    }

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        Payload $payload
    ): ResponseInterface {
        return ($this->responder)($request, $response, $payload);
    }

    public function withTemplate(string $template): static
    {
        $cloned            = clone $this;
        $cloned->responder = $this->responder->withTemplate($template);

        return $cloned;
    }
}
