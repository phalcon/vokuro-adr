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

namespace Vokuro\Tests\Support;

use Phalcon\Container\Container;
use Phalcon\Contracts\ADR\Application as ApplicationInterface;
use Vokuro\AppFront;

/**
 * Exposes the Application so a test can handle a request without emitting.
 * `AbstractHttpFront::run()` is final and emits, but it builds the Application
 * from a protected seam, which this surfaces. The container comes from the
 * public `AbstractHttpFront::boot()`.
 */
final class TestableAppFront extends AppFront
{
    public function application(Container $container): ApplicationInterface
    {
        return $this->getApplication($container);
    }
}
