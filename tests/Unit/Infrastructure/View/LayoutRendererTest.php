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

namespace Vokuro\Tests\Unit\Infrastructure\View;

use Phalcon\Http\Request;
use Phalcon\Mvc\View\Simple;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Meta;
use Vokuro\Infrastructure\View\LayoutRenderer;
use Vokuro\Tests\Support\Fake\FakeSession;

use function dirname;

final class LayoutRendererTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_URI']);

        parent::tearDown();
    }

    /**
     * Unit Tests Vokuro\Infrastructure\View\LayoutRenderer :: derives the Meta and section
     */
    public function testDerivesMetaAndSectionFromTheSession(): void
    {
        $session = new FakeSession(['auth' => ['name' => 'Sarah']]);

        $html = $this->renderer($session, '/users/edit/3')->render('probe');

        $this->assertSame('LAYOUT[users]PAGE[1|Sarah]', $html);
    }

    /**
     * Unit Tests Vokuro\Infrastructure\View\LayoutRenderer :: a payload's own Meta wins
     */
    public function testPayloadMetaWinsOverTheSession(): void
    {
        $session = new FakeSession();

        $html = $this->renderer($session, '/')->render('probe', ['extras' => new Meta(true, 'Override')]);

        $this->assertSame('LAYOUT[index]PAGE[1|Override]', $html);
    }

    /**
     * Unit Tests Vokuro\Infrastructure\View\LayoutRenderer :: a guest renders the least privileged Meta
     */
    public function testGuestRendersClosed(): void
    {
        $session = new FakeSession();

        $html = $this->renderer($session, '/profiles')->render('probe');

        $this->assertSame('LAYOUT[profiles]PAGE[0|]', $html);
    }

    private function renderer(FakeSession $session, string $uri): LayoutRenderer
    {
        $_SERVER['REQUEST_URI'] = $uri;

        $view = new Simple();
        $view->setViewsDir(dirname(__DIR__, 3) . '/Support/fixtures/views/');

        return new LayoutRenderer($view, new Request(), $session, 'layouts/probe');
    }
}
