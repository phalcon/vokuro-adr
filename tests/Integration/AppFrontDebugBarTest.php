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

use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DebugBar\Debug;
use Phalcon\Di\Di;
use Phalcon\Http\ResponseInterface;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Action\Get;
use Vokuro\Tests\Support\TestableAppFront;

use function array_column;
use function dirname;
use function implode;
use function putenv;

/**
 * @runTestsInSeparateProcesses
 *
 * A real boot of the application with the debug bar. The bar is static and the
 * session provider calls `session_start()`, so each test runs in its own process.
 */
final class AppFrontDebugBarTest extends AbstractUnitTestCase
{
    private const BAR = 'id="phalcon-debugbar"';

    /**
     * @return array<string, array{0: string}>
     */
    public static function otherEnvironments(): array
    {
        return [
            'production' => ['production'],
            'test'       => ['test'],
        ];
    }

    /**
     * Integration Tests Vokuro\AppFront :: the bar is absent from a JSON response
     */
    public function testBarIsAbsentFromAJsonResponse(): void
    {
        putenv('APP_ENV=development');

        $response = $this->handle('/no-such-page', 'application/json');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringNotContainsString(self::BAR, (string) $response->getContent());
    }

    /**
     * Integration Tests Vokuro\AppFront :: the bar is absent outside development
     *
     * @dataProvider otherEnvironments
     */
    public function testBarIsAbsentOutsideDevelopment(string $environment): void
    {
        putenv('APP_ENV=' . $environment);

        $response = $this->handle('/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringNotContainsString(self::BAR, (string) $response->getContent());
        $this->assertNull(Debug::getBar());
    }

    /**
     * Integration Tests Vokuro\AppFront :: the bar renders on an HTML page in development
     */
    public function testBarRendersOnAnHtmlPageInDevelopment(): void
    {
        putenv('APP_ENV=development');

        $response = $this->handle('/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString(self::BAR, (string) $response->getContent());
    }

    /**
     * Integration Tests Vokuro\AppFront :: the database panel shows a query of the connection service
     */
    public function testDatabasePanelShowsAQuery(): void
    {
        putenv('APP_ENV=development');
        putenv('DB_NAME=vokuro_adr_test');

        $container = (new TestableAppFront(dirname(__DIR__, 2)))->boot();
        $container->get(Connection::class)->fetchValue('SELECT 1');

        $envelope = $this->envelope('database');

        $this->assertSame(1, $envelope['badge']);
        $this->assertSame('SELECT 1', $envelope['panel'][0]['message']);
    }

    /**
     * Integration Tests Vokuro\AppFront :: no Di is created in the delivery ring
     */
    public function testDeliveryRingHasNoDi(): void
    {
        putenv('APP_ENV=development');

        $this->handle('/');

        $this->assertNull(Di::getDefault());
    }

    /**
     * Integration Tests Vokuro\AppFront :: the error page shows the bar and the caught exception
     */
    public function testExceptionsPanelShowsACaughtException(): void
    {
        putenv('APP_ENV=development');

        $response = $this->handle('/no-such-page');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString(self::BAR, (string) $response->getContent());
        $this->assertSame(1, $this->envelope('exceptions')['badge']);
    }

    /**
     * Integration Tests Vokuro\AppFront :: the route panel shows the action
     */
    public function testRoutePanelShowsTheAction(): void
    {
        putenv('APP_ENV=development');

        $this->handle('/');

        $this->assertSame(Get::class, $this->envelope('route')['panel']['Action']);
    }

    /**
     * Integration Tests Vokuro\AppFront :: the view panel shows the template and the layout
     */
    public function testViewPanelShowsTheTemplateAndTheLayout(): void
    {
        putenv('APP_ENV=development');

        $this->handle('/');

        $paths = implode(' ', array_column($this->envelope('view')['panel'], 'message'));

        $this->assertStringContainsString('index/index', $paths);
        $this->assertStringContainsString('layouts/public', $paths);
    }

    /**
     * @return array<string, mixed>
     */
    private function envelope(string $collector): array
    {
        $bar = Debug::getBar();

        $this->assertNotNull($bar);

        return $bar->getCollector($collector)->collect();
    }

    private function handle(string $uri, string $accept = 'text/html'): ResponseInterface
    {
        $_SERVER['REQUEST_URI']    = $uri;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_ACCEPT']    = $accept;

        $front     = new TestableAppFront(dirname(__DIR__, 2));
        $container = $front->boot();

        return $front->application($container)->handle(
            $container->get(AttributeRequest::class)
        );
    }
}
