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

use Phalcon\ADR\Responder\JsonResponder;
use Phalcon\ADR\Responder\ViewResponder;
use Phalcon\Contracts\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Responder\Responder;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;

use function str_contains;

/**
 * The chain the error responder builds its answer with.
 *
 * A browser asking for HTML gets the error page; anything else keeps the JSON
 * body, so an API client is unaffected.
 */
final class ErrorPageResponder implements Responder
{
    public function __construct(
        private ViewResponder $page,
        private JsonResponder $json
    ) {
    }

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        Payload $payload
    ): ResponseInterface {
        if (false === $this->wantsHtml($request)) {
            return ($this->json)($request, $response, $payload);
        }

        return ($this->page->withTemplate('errors/error'))(
            $request,
            $response,
            $payload
        );
    }

    private function wantsHtml(RequestInterface $request): bool
    {
        return str_contains(
            (string) $request->getHeader('HTTP_ACCEPT'),
            'text/html'
        );
    }
}
