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

namespace Vokuro\Tests\Unit\Action\Profiles;

use Vokuro\Action\Profiles\Delete\GetProfilesDelete;
use Vokuro\Domain\Model\Profile;
use Vokuro\Tests\Support\Fake\FakeProfileRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetProfilesDeleteTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Profiles\Delete\GetProfilesDelete :: deletes the profile and returns to the list
     */
    public function testDeletesAndRedirects(): void
    {
        $profiles = (new FakeProfileRepository())->seed(new Profile(3, 'Auditors', true));

        $response = (new GetProfilesDelete($profiles, $this->redirectResponder()))(
            $this->request(attributes: ['id' => 3])
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/profiles', $response->getHeaders()->get('Location'));
        $this->assertSame([3], $profiles->deleted);
    }

    /**
     * Unit Tests Vokuro\Action\Profiles\Delete\GetProfilesDelete :: a missing profile still returns to the list
     */
    public function testMissingProfileStillRedirects(): void
    {
        $profiles = new FakeProfileRepository();

        $response = (new GetProfilesDelete($profiles, $this->redirectResponder()))(
            $this->request(attributes: ['id' => 999])
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame([], $profiles->deleted);
    }
}
