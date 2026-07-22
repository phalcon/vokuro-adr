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
use Vokuro\AppFront;

/**
 * Exposes the wired container so a test can boot the application without
 * dispatching or emitting. `AbstractHttpFront::run()` is final and emits, but
 * it only builds the container from these protected seams, which this surfaces.
 */
final class TestableAppFront extends AppFront
{
    public function boot(): Container
    {
        $container = $this->buildContainer();

        $this->loadEnvironment($container);
        $this->registerProviders($container);

        return $container;
    }
}
