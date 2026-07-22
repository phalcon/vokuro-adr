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

use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Form;

/**
 * The password fields of the change password form. It declares no validators:
 * the rules are decided by `Vokuro\Domain\Users\ChangePassword`.
 */
class ChangePasswordForm extends Form
{
    public function initialize(): void
    {
        $password = new Password('password');
        $password->clear();

        $this->add($password);

        $confirmPassword = new Password('confirmPassword');
        $confirmPassword->clear();

        $this->add($confirmPassword);
    }
}
