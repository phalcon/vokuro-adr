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

namespace Vokuro\Forms;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;

/**
 * The text fields of a user. It declares no validators: the rules are decided
 * by the create and edit domains.
 *
 * The profile, banned, suspended and active selects are not form elements:
 * `Phalcon\Forms\Element\Select` renders through the legacy `Phalcon\Tag`, so
 * the ADR views build those with the `TagFactory` select helper instead.
 */
class UserForm extends Form
{
    public function initialize(): void
    {
        $this->add(new Text('name', ['placeholder' => 'Name']));
        $this->add(new Text('email', ['placeholder' => 'E-Mail']));
    }
}
