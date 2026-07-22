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

use Vokuro\Contracts\Repository\PasswordChangeRepository;
use Vokuro\Domain\Collection\PasswordChangeCollection;

/**
 * In-memory {@see PasswordChangeRepository}.
 */
final class FakePasswordChangeRepository implements PasswordChangeRepository
{
    /** @var array<int, array{userId: int, ipAddress: string, userAgent: string}> */
    public array $added = [];

    public function add(int $userId, string $ipAddress, string $userAgent): void
    {
        $this->added[] = ['userId' => $userId, 'ipAddress' => $ipAddress, 'userAgent' => $userAgent];
    }

    public function forUser(int $userId): PasswordChangeCollection
    {
        return new PasswordChangeCollection([]);
    }
}
