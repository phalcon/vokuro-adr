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

namespace Vokuro\Tests\Unit\Domain;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Collection\UserCollection;
use Vokuro\Domain\Page;

final class PageTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Page :: holds the slice and its position
     */
    public function testHoldsTheSlice(): void
    {
        $page = new Page(new UserCollection(), current: 2, last: 5, total: 47);

        $this->assertInstanceOf(UserCollection::class, $page->items);
        $this->assertSame(2, $page->current);
        $this->assertSame(5, $page->last);
        $this->assertSame(47, $page->total);
    }

    /**
     * Unit Tests Vokuro\Domain\Page :: next() and previous() are clamped
     */
    public function testNextAndPreviousAreClamped(): void
    {
        $middle = new Page(new UserCollection(), current: 3, last: 5, total: 50);
        $this->assertSame(4, $middle->next());
        $this->assertSame(2, $middle->previous());

        $first = new Page(new UserCollection(), current: 1, last: 5, total: 50);
        $this->assertSame(1, $first->previous());

        $lastPage = new Page(new UserCollection(), current: 5, last: 5, total: 50);
        $this->assertSame(5, $lastPage->next());
    }
}
