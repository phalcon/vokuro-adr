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
use Vokuro\Contracts\Repository\UserRepository;

/**
 * Saves changes to an existing user. The rules the form used to declare live
 * here, keyed by field so each message sits under its input.
 */
final class UpdateUser
{
    public function __construct(
        private UserRepository $users
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $id = (int) $input->get('id');

        if (null === $this->users->findById($id)) {
            return Payload::notFound(['User was not found.']);
        }

        $name       = trim((string) $input->get('name'));
        $email      = trim((string) $input->get('email'));
        $profilesId = (int) $input->get('profilesId');

        $messages = $this->validate($id, $name, $email, $profilesId);

        if ([] !== $messages) {
            return Payload::invalid($messages);
        }

        $this->users->update(
            $id,
            [
                'name'       => $name,
                'email'      => $email,
                'profilesId' => $profilesId,
                'banned'     => $this->flag($input->get('banned')),
                'suspended'  => $this->flag($input->get('suspended')),
                'active'     => $this->flag($input->get('active')),
            ]
        );

        return Payload::updated(['id' => $id])
            ->withMessages(['User was updated successfully.']);
    }

    private function flag(mixed $value): string
    {
        return 'Y' === $value ? 'Y' : 'N';
    }

    /**
     * @return array<string, string> field => message, empty when acceptable
     */
    private function validate(int $id, string $name, string $email, int $profilesId): array
    {
        $messages = [];

        if ('' === $name) {
            $messages['name'] = 'The name is required';
        }

        if ('' === $email) {
            $messages['email'] = 'The e-mail is required';
        } elseif (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $messages['email'] = 'The e-mail is not valid';
        } else {
            $existing = $this->users->findByEmail($email);
            if (null !== $existing && $existing->id !== $id) {
                $messages['email'] = 'That e-mail is already registered';
            }
        }

        if (0 === $profilesId) {
            $messages['profilesId'] = 'A profile is required';
        }

        return $messages;
    }
}
