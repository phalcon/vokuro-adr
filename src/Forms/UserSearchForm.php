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
 * The text fields of the user search toolbar. It declares no validators: an
 * empty or partial filter is a valid search.
 *
 * The profile dropdown is not a form element here. `Phalcon\Forms\Element\Select`
 * renders through the legacy `Phalcon\Tag`, which requires a
 * `Phalcon\Di\DiInterface` the ADR container is not, so the select is built with
 * the `TagFactory` select helper in the template instead.
 */
class UserSearchForm extends Form
{
    public function initialize(): void
    {
        $this->add(new Text('id'));
        $this->add(new Text('name'));
        $this->add(new Text('email'));
    }
}
