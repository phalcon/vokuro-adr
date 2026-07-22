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

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;

/**
 * Renders the fields of the login form.
 *
 * It declares no validators: whether these credentials are acceptable is
 * decided by `Vokuro\Domain\Session\Login`, and a second set of rules here
 * would be one that never runs.
 *
 * The elements resolve their tag factory from the form when they render, so
 * `setTagFactory()` after construction is enough - no container involved.
 */
class LoginForm extends Form
{
    public function initialize(): void
    {
        $this->add(
            new Text('email', [
                'placeholder' => 'Email',
            ])
        );

        $password = new Password('password', [
            'placeholder' => 'Password',
        ]);
        $password->clear();

        $this->add($password);

        $remember = new Check('remember', [
            'value' => 'yes',
            'id'    => 'login-remember',
        ]);
        $remember->setLabel('Remember me');

        $this->add($remember);

        $this->add(
            new Submit('Login', [
                'class' => 'btn',
            ])
        );
    }
}
