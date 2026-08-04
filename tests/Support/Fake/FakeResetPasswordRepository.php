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

use Vokuro\Contracts\Repository\ResetPasswordRepository;
use Vokuro\Domain\Collection\ResetPasswordCollection;
use Vokuro\Domain\Model\ResetPassword;

/**
 * In-memory {@see ResetPasswordRepository}.
 */
final class FakeResetPasswordRepository implements ResetPasswordRepository
{
    /** @var array<int, array{userId: int, code: string}> */
    public array $added = [];

    /** @var array<string, ResetPassword> */
    public array $byCode = [];

    /** @var array<int, int> */
    public array $spent = [];

    public function seed(string $code, ResetPassword $reset): self
    {
        $this->byCode[$code] = $reset;

        return $this;
    }

    public function add(int $userId, string $code): string
    {
        $this->added[] = ['userId' => $userId, 'code' => $code];

        return $code;
    }

    public function findByCode(string $code): ?ResetPassword
    {
        return $this->byCode[$code] ?? null;
    }

    public function forUser(int $userId): ResetPasswordCollection
    {
        return new ResetPasswordCollection([]);
    }

    public function markReset(int $id): void
    {
        $this->spent[] = $id;
    }
}
