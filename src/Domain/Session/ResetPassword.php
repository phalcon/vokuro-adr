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

/**
 * Spends the code {@see ForgotPassword} mailed, and identifies the user it was
 * issued for.
 *
 * It does not set the new password - that is `Users\ChangePassword`, behind the
 * form the action sends the user to. All this decides is whether the link is
 * still worth honoring, so the code is spent here and cannot be replayed.
 *
 * The outcome tells the action what to do next: an unknown code goes home, a
 * spent one goes to the login form, and a live one hands back the identity to
 * sign in with.
 */
final class ResetPassword
{
    public function __construct(
        private ResetPasswordRepository $resetPasswords,
        private UserRepository $users
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $reset = $this->resetPasswords->findByCode((string) $input->get('code'));

        if (null === $reset) {
            return Payload::notFound(['The reset code is not valid']);
        }

        if (true === $reset->reset) {
            return Payload::invalid(['The reset code has already been used']);
        }

        $user = $this->users->findById($reset->usersId);

        if (null === $user) {
            return Payload::notFound(['The reset code is not valid']);
        }

        $this->resetPasswords->markReset($reset->id);

        return Payload::updated([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'profilesId' => $user->profileId,
        ]);
    }
}
