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

use Vokuro\Contracts\Mailer;

use function count;

/**
 * A {@see Mailer} that records messages instead of sending them.
 */
final class FakeMailer implements Mailer
{
    /** @var array<int, array{to: array<string, string>, subject: string, template: string, params: array<string, mixed>}> */
    public array $sent = [];

    public function send(array $to, string $subject, string $template, array $params = []): int
    {
        $this->sent[] = ['to' => $to, 'subject' => $subject, 'template' => $template, 'params' => $params];

        return count($to);
    }
}
