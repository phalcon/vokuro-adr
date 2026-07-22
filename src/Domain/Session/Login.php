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
use Vokuro\Contracts\Repository\FailedLoginRepository;
use Vokuro\Contracts\Repository\SuccessLoginRepository;
use Vokuro\Contracts\Repository\UserRepository;

/**
 * Authenticates a set of credentials.
 *
 * It knows nothing about HTTP, sessions or templates: it answers whether these
 * credentials identify a user who is allowed in, and who that user is. Failed
 * attempts are counted per address and block further tries for a while; a sign
 * in that succeeds is recorded.
 */
final class Login
{
    private const THROTTLE_LIMIT  = 5;
    private const THROTTLE_WINDOW = 900;

    public function __construct(
        private UserRepository $users,
        private SuccessLoginRepository $successLogins,
        private FailedLoginRepository $failedLogins,
        private Security $security
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $email     = (string) $input->get('email');
        $password  = (string) $input->get('password');
        $ipAddress = (string) $input->get('ipAddress');

        if ('' === $email || '' === $password) {
            return Payload::invalid(['The e-mail and the password are required']);
        }

        $recent = $this->failedLogins->recentCount(
            $ipAddress,
            time() - self::THROTTLE_WINDOW
        );

        if ($recent >= self::THROTTLE_LIMIT) {
            return Payload::forbidden(
                ['Too many failed attempts. Please try again in a few minutes.']
            );
        }

        $user = $this->users->findByEmail($email);

        /**
         * The password is always checked, even when the e-mail is unknown, so
         * that a missing account cannot be told apart by how long the answer
         * takes.
         */
        $hash    = $user?->passwordHash ?? '$2y$08$' . str_repeat('.', 53);
        $correct = $this->security->checkHash($password, $hash);

        if (null === $user || false === $correct) {
            $this->failedLogins->add($user?->id, $ipAddress);

            return Payload::unauthenticated(['Wrong e-mail or password']);
        }

        if (false === $user->active) {
            return Payload::unauthenticated(['The account is not active yet']);
        }

        if (true === $user->banned || true === $user->suspended) {
            return Payload::forbidden(['The account is not allowed to sign in']);
        }

        $this->successLogins->add(
            $user->id,
            (string) $input->get('ipAddress'),
            (string) $input->get('userAgent')
        );

        return Payload::authenticated([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'profilesId' => $user->profileId,
        ]);
    }
}
