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

use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\ADR\Responder\StatusMapper;
use Phalcon\ADR\Responder\ViewResponder;
use Phalcon\Http\Request;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Responder\AuthResponder;
use Vokuro\Responder\PrivateResponder;
use Vokuro\Tests\Support\Fake\FakeRenderer;
use Vokuro\Tests\Support\Fake\FakeSession;

/**
 * Base for action tests: real responders over one shared FakeRenderer, a
 * FakeSession, and a real-Request builder (superglobals + route attributes).
 * Collaborators come from tests/Support/Fake.
 */
abstract class AbstractActionTestCase extends AbstractUnitTestCase
{
    protected FakeRenderer $renderer;

    protected FakeSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new FakeRenderer();
        $this->session  = new FakeSession();
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_GET  = [];
        unset(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $_SERVER['HTTP_USER_AGENT'],
            $_SERVER['REMOTE_ADDR']
        );

        parent::tearDown();
    }

    protected function authResponder(): AuthResponder
    {
        return new AuthResponder($this->viewResponder());
    }

    protected function privateResponder(): PrivateResponder
    {
        return new PrivateResponder($this->viewResponder());
    }

    protected function redirectResponder(): RedirectResponder
    {
        return new RedirectResponder();
    }

    /**
     * @param array<string, mixed>     $post
     * @param array<string, mixed>     $query
     * @param array<int|string, mixed> $attributes route params
     * @param array<string, mixed>     $server
     */
    protected function request(
        array $post = [],
        array $query = [],
        array $attributes = [],
        array $server = []
    ): Request {
        $_POST = $post;
        $_GET  = $query;

        foreach ($server as $key => $value) {
            $_SERVER[$key] = $value;
        }

        $request = new Request();

        foreach ($attributes as $key => $value) {
            $request->getAttributes()->set($key, $value);
        }

        return $request;
    }

    protected function viewResponder(): ViewResponder
    {
        return new ViewResponder($this->renderer, new StatusMapper());
    }
}
