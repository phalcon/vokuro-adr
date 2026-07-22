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

namespace Vokuro\Infrastructure\View;

use Phalcon\Contracts\Html\Helper\Input\SelectData;

/**
 * Feeds the profile list to the `TagFactory` select helper.
 *
 * The profile dropdown appears on the search toolbar and the create and edit
 * forms, so the options are wrapped once here and handed to `fromData()` rather
 * than looped in each template.
 */
final class ProfileSelectData implements SelectData
{
    /**
     * @param array<int, string> $profiles id => name
     */
    public function __construct(
        private array $profiles
    ) {
    }

    public function getAttributes(): array
    {
        return [];
    }

    /**
     * @return array<int, string> value => label
     */
    public function getOptions(): array
    {
        return $this->profiles;
    }
}
