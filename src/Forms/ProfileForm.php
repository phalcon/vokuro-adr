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
 * The name field of a profile. The active select is rendered in the template
 * with the `TagFactory` select helper. It declares no validators: the rules
 * are decided by the create and edit domains.
 */
class ProfileForm extends Form
{
    public function initialize(): void
    {
        $this->add(new Text('name', ['placeholder' => 'Name']));
    }
}
