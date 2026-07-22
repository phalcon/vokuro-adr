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

use Vokuro\AppFront;

$rootPath = dirname(__DIR__);

require_once $rootPath . '/vendor/autoload.php';

exit((new AppFront($rootPath))->run());
