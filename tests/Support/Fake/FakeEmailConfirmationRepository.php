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

use Vokuro\Contracts\Repository\EmailConfirmationRepository;
use Vokuro\Domain\Model\EmailConfirmation;

/**
 * In-memory {@see EmailConfirmationRepository}.
 */
final class FakeEmailConfirmationRepository implements EmailConfirmationRepository
{
    /** @var array<string, EmailConfirmation> */
    public array $byCode = [];

    /** @var array<int, array{userId: int, code: string}> */
    public array $added = [];

    /** @var array<int, int> */
    public array $confirmed = [];

    public function seed(string $code, EmailConfirmation $confirmation): self
    {
        $this->byCode[$code] = $confirmation;

        return $this;
    }

    public function add(int $userId, string $code): string
    {
        $this->added[] = ['userId' => $userId, 'code' => $code];

        return $code;
    }

    public function findByCode(string $code): ?EmailConfirmation
    {
        return $this->byCode[$code] ?? null;
    }

    public function markConfirmed(int $id): void
    {
        $this->confirmed[] = $id;
    }
}
