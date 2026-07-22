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

namespace Vokuro\Action\Terms;

use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Responder\ViewResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

/**
 * A static page. There is nothing to ask a domain for, so the action renders
 * the template with a successful, empty payload.
 */
final class GetTerms implements Action
{
    public function __construct(
        private ViewResponder $responder
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        return ($this->responder->withTemplate('terms/index'))(
            $request,
            new Response(),
            Payload::success()
        );
    }
}
