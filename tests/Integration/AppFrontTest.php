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

namespace Vokuro\Tests\Integration;

use Phalcon\ADR\Application;
use Phalcon\ADR\ErrorResponder;
use Phalcon\Contracts\ADR\Dispatcher as DispatcherContract;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Contracts\Logger\Logger as LoggerInterface;
use Phalcon\Contracts\View\Renderer;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Encryption\Security;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Session\ManagerInterface;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Contracts\Authorization;
use Vokuro\Contracts\Cookies as CookiesInterface;
use Vokuro\Contracts\Mailer as MailerInterface;
use Vokuro\Contracts\Repository\EmailConfirmationRepository as EmailConfirmationRepositoryInterface;
use Vokuro\Contracts\Repository\FailedLoginRepository as FailedLoginRepositoryInterface;
use Vokuro\Contracts\Repository\PasswordChangeRepository as PasswordChangeRepositoryInterface;
use Vokuro\Contracts\Repository\PermissionRepository as PermissionRepositoryInterface;
use Vokuro\Contracts\Repository\ProfileRepository as ProfileRepositoryInterface;
use Vokuro\Contracts\Repository\RememberTokenRepository as RememberTokenRepositoryInterface;
use Vokuro\Contracts\Repository\ResetPasswordRepository as ResetPasswordRepositoryInterface;
use Vokuro\Contracts\Repository\SuccessLoginRepository as SuccessLoginRepositoryInterface;
use Vokuro\Contracts\Repository\UserRepository as UserRepositoryInterface;
use Vokuro\Responder\AuthResponder;
use Vokuro\Responder\PrivateResponder;
use Vokuro\Tests\Support\TestableAppFront;

use function dirname;

/**
 * @runTestsInSeparateProcesses
 *
 * A real boot of the application. The session provider calls `session_start()`,
 * so each test runs in its own process.
 */
final class AppFrontTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

        parent::tearDown();
    }

    /**
     * Integration Tests Vokuro\AppFront :: wires every provider
     */
    public function testWiresEveryProvider(): void
    {
        $container = (new TestableAppFront(dirname(__DIR__, 2)))->boot();

        foreach ($this->services() as $id) {
            $this->assertNotNull($container->get($id));
        }
    }

    /**
     * Integration Tests Vokuro\AppFront :: dispatches the home route to a rendered page
     */
    public function testDispatchesTheHomeRoute(): void
    {
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $container = (new TestableAppFront(dirname(__DIR__, 2)))->boot();

        $response = $container->get(Application::class)->handle(
            $container->get(AttributeRequest::class)
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotSame('', (string) $response->getContent());
    }

    /**
     * Integration Tests Vokuro\AppFront :: a provider reads $_ENV when getenv is empty
     */
    public function testEnvFallsBackToServerEnv(): void
    {
        $container = (new TestableAppFront(dirname(__DIR__, 2)))->boot();

        // Drop the key from getenv(); $_ENV still holds it from the dotenv load,
        // so the UrlInterface provider's env() call takes the $_ENV fallback.
        putenv('APP_BASE_URI');

        $this->assertNotNull($container->get(UrlInterface::class));
    }

    /**
     * The services AppFront registers. Resolving each runs its provider closure.
     *
     * @return array<int, class-string>
     */
    private function services(): array
    {
        return [
            UrlInterface::class,
            ManagerInterface::class,
            Security::class,
            LoggerInterface::class,
            Connection::class,
            UserRepositoryInterface::class,
            ResetPasswordRepositoryInterface::class,
            EmailConfirmationRepositoryInterface::class,
            ProfileRepositoryInterface::class,
            FailedLoginRepositoryInterface::class,
            SuccessLoginRepositoryInterface::class,
            PasswordChangeRepositoryInterface::class,
            PermissionRepositoryInterface::class,
            Authorization::class,
            DispatcherContract::class,
            CookiesInterface::class,
            RememberTokenRepositoryInterface::class,
            Renderer::class,
            MailerInterface::class,
            ErrorResponder::class,
            AuthResponder::class,
            PrivateResponder::class,
        ];
    }
}
