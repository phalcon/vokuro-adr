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
 * The text fields of the profile search toolbar.
 */
class ProfileSearchForm extends Form
{
    public function initialize(): void
    {
        $this->add(new Text('id'));
        $this->add(new Text('name'));
    }
}
