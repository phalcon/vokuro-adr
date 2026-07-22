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

namespace Vokuro\Domain\Confirm;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Payload\Payload as PayloadInterface;
use Vokuro\Contracts\Repository\EmailConfirmationRepository;
use Vokuro\Contracts\Repository\UserRepository;

/**
 * Confirms an address from the code in the e-mail, and activates the account.
 *
 * The outcome tells the action what to do next: an unknown code goes home, an
 * already used code goes to the login form, and a fresh confirmation activates
 * the user and reports whether they still owe a password change.
 */
final class ConfirmEmail
{
    public function __construct(
        private EmailConfirmationRepository $confirmations,
        private UserRepository $users
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $confirmation = $this->confirmations->findByCode((string) $input->get('code'));

        if (null === $confirmation) {
            return Payload::notFound(['The confirmation code is not valid']);
        }

        if (true === $confirmation->confirmed) {
            return Payload::invalid(['The address has already been confirmed']);
        }

        $user = $this->users->findById($confirmation->usersId);

        if (null === $user) {
            return Payload::notFound(['The confirmation code is not valid']);
        }

        $this->users->update($user->id, ['active' => 'Y']);
        $this->confirmations->markConfirmed($confirmation->id);

        return Payload::updated([
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'profilesId'         => $user->profileId,
            'mustChangePassword' => $user->mustChangePassword,
        ]);
    }
}
