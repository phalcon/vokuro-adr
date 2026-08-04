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

namespace Vokuro\Tests\Unit\Action;

use Phalcon\ADR\Exceptions\RouteNotFound;
use Phalcon\ADR\Router\AttributeFilter;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Action\Confirm\GetConfirm;
use Vokuro\Action\Profiles\Delete\GetProfilesDelete;
use Vokuro\Action\Profiles\Edit\GetProfilesEdit;
use Vokuro\Action\Profiles\Edit\PostProfilesEdit;
use Vokuro\Action\ResetPassword\GetResetPassword;
use Vokuro\Action\Users\Delete\GetUsersDelete;
use Vokuro\Action\Users\Edit\GetUsersEdit;
use Vokuro\Action\Users\Edit\PostUsersEdit;

/**
 * The `params()` declarations, run through the framework's `AttributeFilter` -
 * the same object the application puts between the router and the dispatcher.
 * These assert the contract an action relies on: by the time it is invoked the
 * segments are named, cast, and already rejected if they were not plausible.
 */
final class RouteParamsTest extends AbstractUnitTestCase
{
    private AttributeFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new AttributeFilter();
    }

    /**
     * Unit Tests route params :: a mailed link names both its segments
     *
     * @dataProvider mailLinkActionProvider
     *
     * @param class-string $class
     */
    public function testMailLinkNamesTheCodeAndLowerCasesTheAddress(string $class): void
    {
        $this->assertSame(
            ['code' => 'AbC123', 'email' => 'user@example.dev'],
            $this->filter->filter($class, ['AbC123', 'User@Example.DEV'])
        );
    }

    /**
     * Unit Tests route params :: an omitted segment leaves the action its default
     *
     * @dataProvider idActionProvider
     *
     * @param class-string $class
     */
    public function testLeavesAnOmittedIdUnset(string $class): void
    {
        $this->assertSame([], $this->filter->filter($class, []));
    }

    /**
     * Unit Tests route params :: the id segment is named and cast to an int
     *
     * @dataProvider idActionProvider
     *
     * @param class-string $class
     */
    public function testNamesAndCastsTheId(string $class): void
    {
        $this->assertSame(['id' => 3], $this->filter->filter($class, ['3']));
    }

    /**
     * Unit Tests route params :: a segment that fails its pattern is a 404
     *
     * @dataProvider rejectedProvider
     *
     * @param class-string $class
     * @param list<string> $segments
     */
    public function testRejectsASegmentThatFailsItsPattern(
        string $class,
        array $segments
    ): void {
        $this->expectException(RouteNotFound::class);

        $this->filter->filter($class, $segments);
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function idActionProvider(): array
    {
        return [
            'user edit'      => [GetUsersEdit::class],
            'user save'      => [PostUsersEdit::class],
            'user delete'    => [GetUsersDelete::class],
            'profile edit'   => [GetProfilesEdit::class],
            'profile save'   => [PostProfilesEdit::class],
            'profile delete' => [GetProfilesDelete::class],
        ];
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function mailLinkActionProvider(): array
    {
        return [
            'confirmation link' => [GetConfirm::class],
            'reset link'        => [GetResetPassword::class],
        ];
    }

    /**
     * @return array<string, array{0: class-string, 1: list<string>}>
     */
    public static function rejectedProvider(): array
    {
        return [
            'a non-numeric id'      => [GetUsersEdit::class, ['abc']],
            'a partly numeric id'   => [GetProfilesDelete::class, ['1x']],
            'a mangled code'        => [GetConfirm::class, ['bad-code', 'a@b.dev']],
            'a malformed address'   => [GetConfirm::class, ['AbC123', 'not-an-address']],
            'a mangled reset code'  => [GetResetPassword::class, ['bad+code', 'a@b.dev']],
            'a bad reset recipient' => [GetResetPassword::class, ['AbC123', 'nope']],
        ];
    }
}
