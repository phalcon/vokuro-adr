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
use Vokuro\Contracts\Repository\PasswordChangeRepository;
use Vokuro\Contracts\Repository\UserRepository;

/**
 * Changes the password of the signed in user. The rules the form used to
 * declare live here. A change clears the must-change flag and is recorded.
 */
final class ChangePassword
{
    private const MINIMUM_PASSWORD = 8;

    public function __construct(
        private UserRepository $users,
        private PasswordChangeRepository $passwordChanges,
        private Security $security
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $userId   = (int) $input->get('userId');
        $password = (string) $input->get('password');
        $confirm  = (string) $input->get('confirmPassword');

        $messages = $this->validate($password, $confirm);

        if ([] !== $messages) {
            return Payload::invalid($messages);
        }

        if (null === $this->users->findById($userId)) {
            return Payload::notFound(['User was not found.']);
        }

        $this->users->update(
            $userId,
            [
                'password'           => $this->security->hash($password),
                'mustChangePassword' => 'N',
            ]
        );

        $this->passwordChanges->add(
            $userId,
            (string) $input->get('ipAddress'),
            (string) $input->get('userAgent')
        );

        return Payload::updated(['id' => $userId])
            ->withMessages(['Your password was successfully changed']);
    }

    /**
     * @return array<string, string> field => message, empty when acceptable
     */
    private function validate(string $password, string $confirm): array
    {
        $messages = [];

        if ('' === $password) {
            $messages['password'] = 'Password is required';
        } elseif (strlen($password) < self::MINIMUM_PASSWORD) {
            $messages['password'] = 'Password is too short. Minimum '
                . self::MINIMUM_PASSWORD . ' characters';
        } elseif ($password !== $confirm) {
            $messages['confirmPassword'] = "Password doesn't match confirmation";
        }

        return $messages;
    }
}
