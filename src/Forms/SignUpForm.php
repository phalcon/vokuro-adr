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
 * Renders the fields of the sign up form.
 *
 * It declares no validators: the rules - required fields, password length,
 * the confirmation matching and the terms - are decided by
 * `Vokuro\Domain\Session\SignUp`.
 */
class SignUpForm extends Form
{
    public function initialize(): void
    {
        $name = new Text('name');
        $name->setLabel('Name');

        $this->add($name);

        $email = new Text('email');
        $email->setLabel('E-Mail');

        $this->add($email);

        $password = new Password('password');
        $password->setLabel('Password');
        $password->clear();

        $this->add($password);

        $confirmPassword = new Password('confirmPassword');
        $confirmPassword->setLabel('Confirm Password');
        $confirmPassword->clear();

        $this->add($confirmPassword);

        $terms = new Check('terms', [
            'value' => 'yes',
            'id'    => 'signup-terms',
        ]);
        $terms->setLabel('Accept terms and conditions');

        $this->add($terms);

        $this->add(
            new Submit('Sign Up', [
                'class' => 'btn',
            ])
        );
    }
}
