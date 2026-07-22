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

use Vokuro\Action\Users\GetUsersEdit;
use Vokuro\Domain\Model\User;
use Vokuro\Tests\Support\Fake\FakePasswordChangeRepository;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Support\Fake\FakeResetPasswordRepository;
use Vokuro\Tests\Support\Fake\FakeSuccessLoginRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetUsersEditTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Users\GetUsersEdit :: renders the edit form for an existing user
     */
    public function testRendersEditForm(): void
    {
        $users = (new FakeUserRepository())->seed(
            new User(3, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, false)
        );

        $response = $this->action($users)($this->request(attributes: [0 => 3]));

        $this->assertSame('users/edit', $this->renderer->calls[0]['path']);
        $this->assertSame(200, $response->getStatusCode());

        $result = $this->renderer->calls[0]['params']['result'];
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('logins', $result);
    }

    /**
     * Unit Tests Vokuro\Action\Users\GetUsersEdit :: a missing user returns to the list
     */
    public function testMissingUserRedirects(): void
    {
        $response = $this->action(new FakeUserRepository())($this->request(attributes: [0 => 999]));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/users', $response->getHeaders()->get('Location'));
    }

    private function action(FakeUserRepository $users): GetUsersEdit
    {
        return new GetUsersEdit(
            $users,
            new FakeProfileRepository(),
            new FakeSuccessLoginRepository(),
            new FakePasswordChangeRepository(),
            new FakeResetPasswordRepository(),
            $this->privateResponder(),
            $this->redirectResponder()
        );
    }
}
