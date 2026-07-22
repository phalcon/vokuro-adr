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

namespace Vokuro\Tests\Support\Fake;

use Phalcon\Contracts\ADR\Handler;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

/**
 * A pipeline {@see Handler} that returns a canned response and remembers
 * whether the middleware passed the request through to it.
 */
final class FakeHandler implements Handler
{
    public bool $called = false;

    public ?AttributeRequest $request = null;

    public function __construct(private ResponseInterface $response = new Response())
    {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $this->called  = true;
        $this->request = $request;

        return $this->response;
    }
}
