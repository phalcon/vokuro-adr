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

use Dotenv\Dotenv;
use Phalcon\Talon\Settings;
use Phalcon\Talon\Talon;

require_once dirname(__FILE__, 2) . '/vendor/autoload.php';

/**
 * Real OS/CI environment variables win over `.env.test`, so the same suite runs
 * in docker (service-name hosts from the container) and in native CI (localhost
 * from `.env.test`).
 */
Dotenv::createImmutable(__DIR__, '.env.test')->safeLoad();

Talon::boot(Settings::fromEnv());
