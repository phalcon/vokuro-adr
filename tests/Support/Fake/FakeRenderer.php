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

use Phalcon\Contracts\View\Renderer;

/**
 * A {@see Renderer} that returns a fixed string and records what it was asked
 * to render, so a responder test can assert on the template and data.
 */
final class FakeRenderer implements Renderer
{
    /** @var array<int, array{path: string, params: array<string, mixed>}> */
    public array $calls = [];

    public function __construct(private string $output = 'rendered')
    {
    }

    public function render(string $path, array $params = []): string
    {
        $this->calls[] = ['path' => $path, 'params' => $params];

        return $this->output;
    }
}
