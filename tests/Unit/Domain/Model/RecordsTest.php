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

namespace Vokuro\Tests\Unit\Domain\Model;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Model\EmailConfirmation;
use Vokuro\Domain\Model\PasswordChange;
use Vokuro\Domain\Model\ResetPassword;
use Vokuro\Domain\Model\SuccessLogin;

final class RecordsTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Model\SuccessLogin :: holds its fields
     */
    public function testSuccessLogin(): void
    {
        $record = new SuccessLogin(id: 1, ipAddress: '10.0.0.1', userAgent: 'curl');

        $this->assertSame(1, $record->id);
        $this->assertSame('10.0.0.1', $record->ipAddress);
        $this->assertSame('curl', $record->userAgent);
    }

    /**
     * Unit Tests Vokuro\Domain\Model\PasswordChange :: holds its fields
     */
    public function testPasswordChange(): void
    {
        $record = new PasswordChange(id: 2, ipAddress: '10.0.0.2', userAgent: 'ff', createdAt: 1700000000);

        $this->assertSame(2, $record->id);
        $this->assertSame('10.0.0.2', $record->ipAddress);
        $this->assertSame('ff', $record->userAgent);
        $this->assertSame(1700000000, $record->createdAt);
    }

    /**
     * Unit Tests Vokuro\Domain\Model\ResetPassword :: holds its fields
     */
    public function testResetPassword(): void
    {
        $record = new ResetPassword(id: 3, createdAt: 1700000001, reset: true);

        $this->assertSame(3, $record->id);
        $this->assertSame(1700000001, $record->createdAt);
        $this->assertTrue($record->reset);
    }

    /**
     * Unit Tests Vokuro\Domain\Model\EmailConfirmation :: holds its fields
     */
    public function testEmailConfirmation(): void
    {
        $record = new EmailConfirmation(id: 4, usersId: 9, confirmed: false);

        $this->assertSame(4, $record->id);
        $this->assertSame(9, $record->usersId);
        $this->assertFalse($record->confirmed);
    }
}
