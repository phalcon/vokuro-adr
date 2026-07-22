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

namespace Vokuro\Tests\Unit\Domain\Profiles;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Status;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Profiles\CreateProfile;
use Vokuro\Domain\Profiles\UpdateProfile;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;

final class ProfileDomainsTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Profiles\CreateProfile :: requires a name
     */
    public function testCreateRequiresName(): void
    {
        $profiles = new FakeProfileRepository();

        $payload = (new CreateProfile($profiles))(new Input(['name' => ' ', 'active' => 'Y']));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertSame([], $profiles->added);
    }

    /**
     * Unit Tests Vokuro\Domain\Profiles\CreateProfile :: stores a profile
     */
    public function testCreateStores(): void
    {
        $profiles = new FakeProfileRepository();

        $payload = (new CreateProfile($profiles))(new Input(['name' => 'Auditors', 'active' => 'Y']));

        $this->assertSame(Status::CREATED, $payload->getStatus());
        $this->assertSame(['name' => 'Auditors', 'active' => 'Y'], $profiles->added[1]);
    }

    /**
     * Unit Tests Vokuro\Domain\Profiles\UpdateProfile :: reports a missing profile
     */
    public function testUpdateNotFound(): void
    {
        $profiles = new FakeProfileRepository();

        $payload = (new UpdateProfile($profiles))(new Input(['id' => 99, 'name' => 'X']));

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
        $this->assertSame([], $profiles->updated);
    }

    /**
     * Unit Tests Vokuro\Domain\Profiles\UpdateProfile :: requires a name
     */
    public function testUpdateRequiresName(): void
    {
        $profiles = new FakeProfileRepository();
        $profiles->seed(new Profile(1, 'Admins', true));

        $payload = (new UpdateProfile($profiles))(new Input(['id' => 1, 'name' => '']));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertSame([], $profiles->updated);
    }

    /**
     * Unit Tests Vokuro\Domain\Profiles\UpdateProfile :: saves the changes
     */
    public function testUpdateSaves(): void
    {
        $profiles = new FakeProfileRepository();
        $profiles->seed(new Profile(1, 'Admins', true));

        $payload = (new UpdateProfile($profiles))(
            new Input(['id' => 1, 'name' => 'Admins', 'active' => 'N'])
        );

        $this->assertSame(Status::UPDATED, $payload->getStatus());
        $this->assertSame(['name' => 'Admins', 'active' => 'N'], $profiles->updated[1]);
    }
}
