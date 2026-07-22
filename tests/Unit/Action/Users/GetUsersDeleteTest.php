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

use Vokuro\Action\Users\GetUsersDelete;
use Vokuro\Domain\Model\User;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetUsersDeleteTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Users\GetUsersDelete :: deletes the user and returns to the list
     */
    public function testDeletesAndRedirects(): void
    {
        $users = (new FakeUserRepository())->seed(
            new User(3, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, false)
        );

        $response = (new GetUsersDelete($users, $this->redirectResponder()))(
            $this->request(attributes: [0 => 3])
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
        $this->assertSame([3], $users->deleted);
    }

    /**
     * Unit Tests Vokuro\Action\Users\GetUsersDelete :: a missing user still returns to the list
     */
    public function testMissingUserStillRedirects(): void
    {
        $users = new FakeUserRepository();

        $response = (new GetUsersDelete($users, $this->redirectResponder()))(
            $this->request(attributes: [0 => 999])
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame([], $users->deleted);
    }
}
