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

namespace Vokuro\Tests\Unit\Action\Users;

use Vokuro\Action\Users\GetUsersCreate;
use Vokuro\Domain\Model\Profile;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetUsersCreateTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Users\GetUsersCreate :: renders the form with the profile options
     */
    public function testRendersFormWithProfiles(): void
    {
        $profiles = (new FakeProfileRepository())->seed(new Profile(2, 'Users', true));

        $response = (new GetUsersCreate($profiles, $this->privateResponder()))($this->request());

        $this->assertSame('users/create', $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([2 => 'Users'], $this->renderer->calls[0]['params']['result']['profiles']);
    }
}
