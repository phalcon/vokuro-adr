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
use Phalcon\ADR\Responder\StatusMapper;
use Phalcon\ADR\Responder\ViewResponder;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Responder\PrivateResponder;
use Vokuro\Tests\Support\Fake\FakeRenderer;

final class PrivateResponderTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Responder\PrivateResponder :: renders its template and maps the status
     */
    public function testRendersAndMapsStatus(): void
    {
        $renderer  = new FakeRenderer('DASHBOARD');
        $responder = new PrivateResponder(new ViewResponder($renderer, new StatusMapper(), 'users/index'));

        $response = $responder(new Request(), new Response(), Payload::success());

        $this->assertSame('DASHBOARD', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('users/index', $renderer->calls[0]['path']);
    }

    /**
     * Unit Tests Vokuro\Responder\PrivateResponder :: withTemplate clones and rebinds the template
     */
    public function testWithTemplateClonesAndRebinds(): void
    {
        $renderer  = new FakeRenderer();
        $responder = new PrivateResponder(new ViewResponder($renderer, new StatusMapper(), 'original'));

        $cloned = $responder->withTemplate('users/edit');

        $this->assertNotSame($responder, $cloned);

        $cloned(new Request(), new Response(), Payload::success());

        $this->assertSame('users/edit', $renderer->calls[0]['path']);
    }
}
