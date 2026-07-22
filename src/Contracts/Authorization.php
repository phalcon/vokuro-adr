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

namespace Vokuro\Contracts;

/**
 * Answers whether a profile may perform an action on a resource.
 *
 * The one place the decision is made. Middleware asks it to guard a request,
 * and a view can ask it to show or hide a control - neither knows how the
 * answer is reached or where the grants are stored.
 */
interface Authorization
{
    public function isAllowed(int $profileId, string $resource, string $action): bool;
}
