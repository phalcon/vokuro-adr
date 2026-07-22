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
     * @param class-string<Form> $formClass
     * @param array<int, string>  $expected
     */
    public function testFormDeclaresItsFields(string $formClass, array $expected): void
    {
        $form = new $formClass();

        $names = array_keys($form->getElements());

        sort($names);
        sort($expected);

        $this->assertSame($expected, $names);
    }

    /**
     * @return array<string, array{0: class-string<Form>, 1: array<int, string>}>
     */
    public static function formProvider(): array
    {
        return [
            'sign up'         => [SignUpForm::class, ['name', 'email', 'password', 'confirmPassword', 'terms', 'Sign Up']],
            'login'           => [LoginForm::class, ['email', 'password', 'remember', 'Login']],
            'change password' => [ChangePasswordForm::class, ['password', 'confirmPassword']],
            'forgot password' => [ForgotPasswordForm::class, ['email', 'Send']],
            'profile'         => [ProfileForm::class, ['name']],
            'profile search'  => [ProfileSearchForm::class, ['id', 'name']],
            'user'            => [UserForm::class, ['name', 'email']],
            'user search'     => [UserSearchForm::class, ['id', 'name', 'email']],
        ];
    }
}
