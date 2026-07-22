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

namespace Vokuro\Tests\Unit\Infrastructure\Mail;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Symfony\Component\Mime\Email;
use Vokuro\Infrastructure\Mail\Mailer;
use Vokuro\Tests\Support\Fake\FakeRenderer;
use Vokuro\Tests\Support\Fake\FakeTransport;

final class MailerTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Infrastructure\Mail\Mailer :: builds the e-mail and hands it to the transport
     */
    public function testSendsBuiltEmail(): void
    {
        $renderer  = new FakeRenderer('<p>Hello</p>');
        $transport = new FakeTransport();
        $mailer    = new Mailer($renderer, 'from@vokuro.dev', 'Vokuro', 'public.url', $transport);

        $count = $mailer->send(['kyle@x.dev' => 'Kyle'], 'Confirm', 'confirmation', ['code' => 'abc']);

        $this->assertSame(1, $count);

        $email = $transport->sent;
        $this->assertInstanceOf(Email::class, $email);
        $this->assertSame('Confirm', $email->getSubject());
        $this->assertSame('<p>Hello</p>', $email->getHtmlBody());
        $this->assertSame('kyle@x.dev', $email->getTo()[0]->getAddress());
        $this->assertSame('Kyle', $email->getTo()[0]->getName());
        $this->assertSame('from@vokuro.dev', $email->getFrom()[0]->getAddress());
        $this->assertSame('Vokuro', $email->getFrom()[0]->getName());
        $this->assertSame('emailTemplates/confirmation', $renderer->calls[0]['path']);
        $this->assertSame('public.url', $renderer->calls[0]['params']['publicUrl']);
    }
}
