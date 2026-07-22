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

namespace Vokuro\Contracts\Repository;

use Vokuro\Domain\Model\EmailConfirmation;

/**
 * Stores the single use codes that confirm an address belongs to the person
 * who registered it.
 */
interface EmailConfirmationRepository
{
    /**
     * Records a code against a user and returns it.
     */
    public function add(int $userId, string $code): string;

    /**
     * The confirmation for a code, or null when there is none.
     */
    public function findByCode(string $code): ?EmailConfirmation;

    /**
     * Marks a confirmation as used.
     */
    public function markConfirmed(int $id): void;
}
