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

namespace Vokuro\Tests\Unit\Responder;

use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Responder\JsonResponder;
use Phalcon\ADR\Responder\StatusMapper;
use Phalcon\ADR\Responder\ViewResponder;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Responder\ErrorPageResponder;
use Vokuro\Tests\Support\Fake\FakeRenderer;

final class ErrorPageResponderTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_ACCEPT']);

        parent::tearDown();
    }

    /**
     * Unit Tests Vokuro\Responder\ErrorPageResponder :: renders the error page for a browser
     */
    public function testRendersErrorPageForHtml(): void
    {
        $renderer               = new FakeRenderer('ERROR PAGE');
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml';

        $response = $this->responder($renderer)(new Request(), new Response(), Payload::error());

        $this->assertSame('ERROR PAGE', $response->getContent());
        $this->assertSame('errors/error', $renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Responder\ErrorPageResponder :: keeps JSON for an API client
     */
    public function testKeepsJsonForApiClients(): void
    {
        $renderer               = new FakeRenderer('ERROR PAGE');
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $response = $this->responder($renderer)(new Request(), new Response(), Payload::error());

        $this->assertSame([], $renderer->calls);
        $this->assertStringContainsString('json', (string) $response->getHeaders()->get('Content-Type'));
    }

    private function responder(FakeRenderer $renderer): ErrorPageResponder
    {
        return new ErrorPageResponder(
            new ViewResponder($renderer, new StatusMapper()),
            new JsonResponder()
        );
    }
}
