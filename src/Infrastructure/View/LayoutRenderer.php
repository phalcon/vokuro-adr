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

namespace Vokuro\Infrastructure\View;

use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Contracts\View\Renderer;
use Phalcon\Mvc\View\Simple;
use Phalcon\Session\ManagerInterface;
use Vokuro\Domain\Meta;

/**
 * Wraps a template in a layout.
 *
 * `Simple` renders a single template and has no view hierarchy, so the page is
 * rendered first, handed to the view as its content, and the layout is rendered
 * second. That keeps `$this->getContent()` working inside the layout, which is
 * what the templates expect.
 */
final class LayoutRenderer implements Renderer
{
    public function __construct(
        private Simple $view,
        private AttributeRequest $request,
        private ManagerInterface $session,
        private string $layout = 'layouts/public'
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function render(string $path, array $params = []): string
    {
        /**
         * On the parameters, not with `setVar()`: `Simple::render()` merges
         * the two and the parameters win, so a payload carrying `null` would
         * overwrite anything set on the view.
         */
        $params['extras'] = $this->extras($params);

        $this->view->setVar('section', $this->section());
        $this->view->setContent(
            $this->view->render($path, $params)
        );

        return $this->view->render($this->layout, $params);
    }

    /**
     * The `Meta` the layout reads. An action that put its own on the payload
     * wins; otherwise the chrome still needs to know whether anyone is signed
     * in, so a default is derived here rather than left empty on every page
     * that did not think to set one.
     *
     * @param array<string, mixed> $params
     */
    private function extras(array $params): Meta
    {
        $extras = $params['extras'] ?? null;

        if ($extras instanceof Meta) {
            return $extras;
        }

        $auth = (array) $this->session->get('auth');

        return new Meta(
            isLoggedIn: $this->session->has('auth'),
            name: (string) ($auth['name'] ?? '')
        );
    }

    /**
     * The part of the site being viewed, taken from the first path segment.
     * `/users/edit/3` is the users section, and the home page keeps the name
     * the menus already use for it.
     *
     * Resolved per render rather than once, so a container that outlives a
     * single request cannot serve a stale one.
     */
    private function section(): string
    {
        $segments = explode('/', trim($this->request->getURI(true), '/'));

        return '' === $segments[0] ? 'index' : $segments[0];
    }
}
