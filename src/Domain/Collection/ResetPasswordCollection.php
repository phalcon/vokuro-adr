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

namespace Vokuro\Domain\Collection;

use Phalcon\Support\Collection;
use Vokuro\Domain\Model\ResetPassword;

/**
 * A typed collection of ResetPassword records, keyed by id.
 *
 * @extends Collection<ResetPassword>
 */
final class ResetPasswordCollection extends Collection
{
    /**
     * @param array<int, ResetPassword> $records
     */
    public function __construct(array $records = [])
    {
        parent::__construct([], false, false, ResetPassword::class);

        foreach ($records as $record) {
            $this->set((string) $record->id, $record);
        }
    }
}
