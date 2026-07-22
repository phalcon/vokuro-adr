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

namespace Vokuro\Tests\Support\Fake;

use Vokuro\Contracts\Repository\RememberTokenRepository;

/**
 * In-memory {@see RememberTokenRepository}, keyed by token hash.
 */
final class FakeRememberTokenRepository implements RememberTokenRepository
{
    /** @var array<string, int> */
    public array $owners = [];

    /** @var array<int, array{userId: int, tokenHash: string, userAgent: string}> */
    public array $added = [];

    /** @var array<int, int> */
    public array $deleted = [];

    public function seed(string $tokenHash, int $userId): self
    {
        $this->owners[$tokenHash] = $userId;

        return $this;
    }

    public function add(int $userId, string $tokenHash, string $userAgent): void
    {
        $this->added[]             = ['userId' => $userId, 'tokenHash' => $tokenHash, 'userAgent' => $userAgent];
        $this->owners[$tokenHash]  = $userId;
    }

    public function deleteForUser(int $userId): void
    {
        $this->deleted[] = $userId;
        $this->owners    = array_filter($this->owners, fn(int $owner): bool => $owner !== $userId);
    }

    public function findUserByToken(string $tokenHash): ?int
    {
        return $this->owners[$tokenHash] ?? null;
    }
}
