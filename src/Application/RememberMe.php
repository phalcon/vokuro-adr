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

namespace Vokuro\Application;

use Vokuro\Contracts\Cookies;
use Vokuro\Contracts\Repository\RememberTokenRepository;
use Vokuro\Contracts\Repository\UserRepository;

/**
 * Recognises a returning visitor from a cookie, so they need not sign in again.
 *
 * A random token is minted at sign in; its hash is stored and only the raw
 * value travels in the cookie, so the stored copy cannot be replayed. Recall
 * rejects a token whose user does not match the companion cookie.
 */
final class RememberMe
{
    private const COOKIE_TOKEN = 'RMT';
    private const COOKIE_USER  = 'RMU';
    private const LIFETIME     = 2592000;

    public function __construct(
        private RememberTokenRepository $tokens,
        private UserRepository $users,
        private Cookies $cookies
    ) {
    }

    public function forget(int $userId): void
    {
        $this->tokens->deleteForUser($userId);
        $this->cookies->delete(self::COOKIE_USER);
        $this->cookies->delete(self::COOKIE_TOKEN);
    }

    /**
     * The session identity to restore, or null when there is no valid cookie.
     *
     * @return array<string, mixed>|null
     */
    public function recall(): ?array
    {
        $userId = (int) ($this->cookies->get(self::COOKIE_USER) ?? 0);
        $raw    = (string) ($this->cookies->get(self::COOKIE_TOKEN) ?? '');

        if (0 === $userId || '' === $raw) {
            return null;
        }

        $owner = $this->tokens->findUserByToken(hash('sha256', $raw));
        if ($owner !== $userId) {
            return null;
        }

        $user = $this->users->findById($userId);
        if (null === $user) {
            return null;
        }

        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'profilesId' => $user->profileId,
        ];
    }

    public function remember(int $userId, string $userAgent): void
    {
        $raw = bin2hex(random_bytes(32));
        $this->tokens->add($userId, hash('sha256', $raw), $userAgent);

        $expires = time() + self::LIFETIME;
        $this->cookies->set(self::COOKIE_USER, (string) $userId, $expires);
        $this->cookies->set(self::COOKIE_TOKEN, $raw, $expires);
    }
}
