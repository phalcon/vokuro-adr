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

use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;

/**
 * Renders the field that asks where to send a reset link.
 *
 * It declares no validators: whether the address is usable is decided by
 * `Vokuro\Domain\Session\ForgotPassword`.
 */
class ForgotPasswordForm extends Form
{
    public function initialize(): void
    {
        $this->add(
            new Text('email', [
                'placeholder' => 'Email',
            ])
        );

        $this->add(
            new Submit('Send', [
                'class' => 'btn',
            ])
        );
    }
}
