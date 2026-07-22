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

namespace Vokuro\Tests\Unit\Forms;

use Phalcon\Forms\Form;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Forms\ChangePasswordForm;
use Vokuro\Forms\ForgotPasswordForm;
use Vokuro\Forms\LoginForm;
use Vokuro\Forms\ProfileForm;
use Vokuro\Forms\ProfileSearchForm;
use Vokuro\Forms\SignUpForm;
use Vokuro\Forms\UserForm;
use Vokuro\Forms\UserSearchForm;

final class FormsTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Forms :: each form declares exactly its fields
     *
     * @dataProvider formProvider
     *
     * @param array<int, string> $expected
     */
    public function testFormDeclaresItsFields(Form $form, array $expected): void
    {
        $names = array_keys($form->getElements());

        sort($names);
        sort($expected);

        $this->assertSame($expected, $names);
    }

    /**
     * @return array<string, array{0: Form, 1: array<int, string>}>
     */
    public static function formProvider(): array
    {
        return [
            'sign up'         => [new SignUpForm(), ['name', 'email', 'password', 'confirmPassword', 'terms', 'Sign Up']],
            'login'           => [new LoginForm(), ['email', 'password', 'remember', 'Login']],
            'change password' => [new ChangePasswordForm(), ['password', 'confirmPassword']],
            'forgot password' => [new ForgotPasswordForm(), ['email', 'Send']],
            'profile'         => [new ProfileForm(), ['name']],
            'profile search'  => [new ProfileSearchForm(), ['id', 'name']],
            'user'            => [new UserForm(), ['name', 'email']],
            'user search'     => [new UserSearchForm(), ['id', 'name', 'email']],
        ];
    }
}
