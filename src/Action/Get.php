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

namespace Vokuro\Action;

use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Responder\ViewResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

/**
 * The home page. The convention router resolves `GET /` to the class named
 * after the verb alone, directly under the base namespace.
 *
 * It does not build a `Meta`: the layout derives the signed-in state for every
 * page that does not carry its own, so the home page needs no special case.
 */
final class Get implements Action
{
    public function __construct(
        private ViewResponder $responder
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        return ($this->responder->withTemplate('index/index'))(
            $request,
            new Response(),
            Payload::success()
        );
    }
}
