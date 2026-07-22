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

namespace Vokuro\Infrastructure\Mail;

use Phalcon\Contracts\View\Renderer;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Sends the application's e-mails.
 *
 * The renderer it is given is bound to the e-mail layout, so a caller names a
 * template and hands over its parameters without knowing any markup.
 */
final class Mailer
{
    public function __construct(
        private Renderer $renderer,
        private string $fromEmail,
        private string $fromName,
        private string $publicUrl,
        private string $server,
        private int $port,
        private string $username = '',
        private string $password = ''
    ) {
    }

    /**
     * @param array<string, string> $to      address => name
     * @param array<string, mixed>  $params
     *
     * @return int the number of addressees
     */
    public function send(
        array $to,
        string $subject,
        string $template,
        array $params = []
    ): int {
        $message = $this->buildMessage(
            $to,
            $subject,
            $this->renderer->render(
                'emailTemplates/' . $template,
                array_merge(['publicUrl' => $this->publicUrl], $params)
            )
        );

        $transport = new EsmtpTransport($this->server, $this->port);
        $transport->setUsername($this->username);
        $transport->setPassword($this->password);

        (new SymfonyMailer($transport))->send($message);

        return count($to);
    }

    /**
     * Nothing is read from anywhere else, so a message can be built and
     * inspected without sending it.
     *
     * @param array<string, string> $to
     */
    private function buildMessage(
        array $to,
        string $subject,
        string $html
    ): Email {
        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->subject($subject)
            ->html($html);

        foreach ($to as $address => $name) {
            $email->addTo(new Address((string) $address, (string) $name));
        }

        return $email;
    }
}
