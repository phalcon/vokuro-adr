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

namespace Vokuro\Application;

/**
 * The set of permissions the application defines.
 *
 * Each resource lists the actions that can be granted on it, and every action
 * has a human name for the permissions screen. This is the catalogue the
 * screen offers; which of them a profile actually holds lives in the database.
 */
final class Acl
{
    private const ACTION_DESCRIPTIONS = [
        'index'          => 'Access',
        'search'         => 'Search',
        'create'         => 'Create',
        'edit'           => 'Edit',
        'delete'         => 'Delete',
        'changePassword' => 'Change password',
    ];

    private const RESOURCES = [
        'users'       => ['index', 'search', 'edit', 'create', 'delete', 'changePassword'],
        'profiles'    => ['index', 'search', 'edit', 'create', 'delete'],
        'permissions' => ['index'],
    ];

    public function actionDescription(string $action): string
    {
        return self::ACTION_DESCRIPTIONS[$action] ?? $action;
    }

    /**
     * @return array<string, list<string>> resource => actions
     */
    public function resources(): array
    {
        return self::RESOURCES;
    }
}
