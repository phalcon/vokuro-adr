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

namespace Vokuro\Domain;

use Phalcon\Support\Collection;

/**
 * One page of a larger result set: the rows for this page and where the page
 * sits in the whole.
 *
 * @template TItem of object
 */
final class Page
{
    /**
     * @param Collection<TItem> $items
     */
    public function __construct(
        /** @var Collection<TItem> */
        public readonly Collection $items,
        public readonly int $current,
        public readonly int $last,
        public readonly int $total
    ) {
    }

    public function next(): int
    {
        return min($this->last, $this->current + 1);
    }

    public function previous(): int
    {
        return max(1, $this->current - 1);
    }
}
