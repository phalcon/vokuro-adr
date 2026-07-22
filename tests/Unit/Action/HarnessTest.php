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

namespace Vokuro\Tests\Unit\Action;

final class HarnessTest extends AbstractActionTestCase
{
    /**
     * Action harness :: a generated CSRF token validates against its Security
     */
    public function testValidCsrfRoundTrips(): void
    {
        [$request, $security] = $this->signedRequest();

        $this->assertTrue($security->checkToken('csrf', $request->getPost('csrf')));
    }

    /**
     * Action harness :: route attributes set on the request are readable
     */
    public function testRouteAttributesAreReadable(): void
    {
        $request = $this->request([], [], [0 => 42]);

        $this->assertSame(42, $request->getAttributes()->get(0));
    }
}
