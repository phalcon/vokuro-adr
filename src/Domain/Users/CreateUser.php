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

namespace Vokuro\Domain\Users;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Payload\Payload as PayloadInterface;
use Phalcon\Encryption\Security;
use Vokuro\Contracts\Repository\EmailConfirmationRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Infrastructure\Mail\Mailer;

/**
 * Creates a user on behalf of an administrator.
 *
 * The account starts inactive with a temporary password, so the person must
 * confirm the address from the e-mail and set their own password on first
 * sign in. The rules the form used to declare live here.
 */
final class CreateUser
{
    public function __construct(
        private UserRepository $users,
        private EmailConfirmationRepository $confirmations,
        private Security $security,
        private Mailer $mailer
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $name       = trim((string) $input->get('name'));
        $email      = trim((string) $input->get('email'));
        $profilesId = (int) $input->get('profilesId');

        $messages = $this->validate($name, $email, $profilesId);

        if ([] !== $messages) {
            return Payload::invalid($messages);
        }

        $userId = $this->users->add(
            [
                'name'               => $name,
                'email'              => $email,
                'password'           => $this->security->hash($this->tempPassword()),
                'profilesId'         => $profilesId,
                'mustChangePassword' => 'Y',
                'banned'             => 'N',
                'suspended'          => 'N',
                'active'             => 'N',
            ]
        );

        $this->confirm($userId, $name, $email);

        return Payload::created(['id' => $userId])
            ->withMessages(['User was created successfully']);
    }

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

    private function tempPassword(): string
    {
        return preg_replace(
            '/[^a-zA-Z0-9]/',
            '',
            base64_encode(random_bytes(12))
        );
    }

    /**
     * @return array<string, string> field => message, empty when acceptable
     */
    private function validate(string $name, string $email, int $profilesId): array
    {
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

        if (0 === $profilesId) {
            $messages['profilesId'] = 'A profile is required';
        }

        return $messages;
    }
}
