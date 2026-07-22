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

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * A Symfony mail {@see TransportInterface} that records the message instead of
 * sending it, so a test can inspect the built e-mail.
 */
final class FakeTransport implements TransportInterface
{
    public ?RawMessage $sent = null;

    public function __toString(): string
    {
        return 'fake://';
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $this->sent = $message;

        return null;
    }
}
