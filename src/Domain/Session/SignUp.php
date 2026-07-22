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
use Phalcon\Encryption\Security;
use Vokuro\Contracts\Mailer;
use Vokuro\Contracts\Repository\EmailConfirmationRepository;
use Vokuro\Contracts\Repository\UserRepository;

/**
 * Registers an account.
 *
 * The rules the sign up form used to declare live here: the form renders
 * fields, this decides whether what came back is acceptable. Messages are
 * keyed by field so the page can put each one under the input it belongs to.
 */
final class SignUp
{
    private const MINIMUM_PASSWORD = 8;

    /**
     * New accounts are ordinary users, and cannot sign in until the address
     * has been confirmed.
     */
    private const PROFILE_USER = 2;

    public function __construct(
        private UserRepository $users,
        private EmailConfirmationRepository $confirmations,
        private Security $security,
        private Mailer $mailer
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $name     = trim((string) $input->get('name'));
        $email    = trim((string) $input->get('email'));
        $password = (string) $input->get('password');
        $confirm  = (string) $input->get('confirmPassword');
        $terms    = (string) $input->get('terms');

        $messages = $this->validate($name, $email, $password, $confirm, $terms);

        if ([] !== $messages) {
            return Payload::invalid($messages);
        }

        $userId = $this->users->add(
            [
                'name'               => $name,
                'email'              => $email,
                'password'           => $this->security->hash($password),
                'profilesId'         => self::PROFILE_USER,
                'mustChangePassword' => 'N',
                'banned'             => 'N',
                'suspended'          => 'N',
                'active'             => 'N',
            ]
        );

        $this->confirm($userId, $name, $email);

        return Payload::created(['id' => $userId, 'email' => $email])
            ->withMessages(
                ['A confirmation mail has been sent to ' . $email]
            );
    }

    /**
     * Issues the confirmation code and mails it.
     */
    private function confirm(int $userId, string $name, string $email): void
    {
        $code = $this->confirmations->add(
            $userId,
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                base64_encode(random_bytes(24))
            )
        );

        $this->mailer->send(
            [$email => $name],
            'Please confirm your email',
            'confirmation',
            ['confirmUrl' => '/confirm/' . $code . '/' . $email]
        );
    }

    /**
     * @return array<string, string> field => message, empty when acceptable
     */
    private function validate(
        string $name,
        string $email,
        string $password,
        string $confirm,
        string $terms
    ): array {
        $messages = [];

        if ('' === $name) {
            $messages['name'] = 'The name is required';
        }

        if ('' === $email) {
            $messages['email'] = 'The e-mail is required';
        } elseif (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $messages['email'] = 'The e-mail is not valid';
        } elseif (null !== $this->users->findByEmail($email)) {
            $messages['email'] = 'That e-mail is already registered';
        }

        if ('' === $password) {
            $messages['password'] = 'The password is required';
        } elseif (strlen($password) < self::MINIMUM_PASSWORD) {
            $messages['password'] = 'Password is too short. Minimum '
                . self::MINIMUM_PASSWORD . ' characters';
        } elseif ($password !== $confirm) {
            $messages['confirmPassword'] = "Password doesn't match confirmation";
        }

        if ('yes' !== $terms) {
            $messages['terms'] = 'Terms and conditions must be accepted';
        }

        return $messages;
    }
}
