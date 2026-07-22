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

namespace Vokuro\Domain\Session;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Payload\Payload as PayloadInterface;
use Vokuro\Contracts\Repository\ResetPasswordRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Model\User;
use Vokuro\Infrastructure\Mail\Mailer;

/**
 * Issues a single use code that lets a user set a new password, and mails it.
 *
 * The answer is the same wheth1er or not the address belongs to an account, so
 * the form cannot be used to find out who has registered.
 */
final class ForgotPassword
{
    private const SENT = 'If that e-mail is registered, a reset link is on its way';

    public function __construct(
        private UserRepository $users,
        private ResetPasswordRepository $resetPasswords,
        private Mailer $mailer
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $email = trim((string) $input->get('email'));

        if ('' === $email || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Payload::invalid(['A valid e-mail address is required']);
        }

        $user = $this->users->findByEmail($email);

        if (null !== $user) {
            $this->mail($user, $this->issueCode($user->id));
        }

        /**
         * `success()` fills the result, and the page shows messages, so the
         * text is set as one.
         */
        return Payload::success()->withMessages([self::SENT]);
    }

    /**
     * @return string the code that was stored
     */
    private function issueCode(int $userId): string
    {
        return $this->resetPasswords->add(
            $userId,
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                base64_encode(random_bytes(24))
            )
        );
    }

    private function mail(User $user, string $code): void
    {
        $this->mailer->send(
            [$user->email => $user->name],
            'Reset your password',
            'reset',
            ['resetUrl' => '/reset-password/' . $code . '/' . $user->email]
        );
    }
}
