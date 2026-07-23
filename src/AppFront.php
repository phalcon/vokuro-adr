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

namespace Vokuro;

use Dotenv\Dotenv;
use Phalcon\ADR\Application;
use Phalcon\ADR\Dispatcher;
use Phalcon\ADR\ErrorResponder;
use Phalcon\ADR\Front\AbstractHttpFront;
use Phalcon\ADR\Responder\JsonResponder;
use Phalcon\ADR\Responder\StatusMapper;
use Phalcon\ADR\Responder\ViewResponder;
use Phalcon\Container\Container;
use Phalcon\Contracts\ADR\Application as ApplicationInterface;
use Phalcon\Contracts\ADR\Dispatcher as DispatcherContract;
use Phalcon\Contracts\Events\Manager as EventsManagerContract;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Contracts\Logger\Logger as LoggerInterface;
use Phalcon\Contracts\View\Renderer;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Encryption\Security;
use Phalcon\Html\TagFactory;
use Phalcon\Logger\Adapter\Stream as LogStream;
use Phalcon\Logger\Logger;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Mvc\View\Simple;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Session\ManagerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Vokuro\Application\Authorizer;
use Vokuro\Application\RememberMe;
use Vokuro\Contracts\Authorization;
use Vokuro\Contracts\Cookies as CookiesInterface;
use Vokuro\Contracts\Csrf as CsrfInterface;
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
use Vokuro\Infrastructure\Http\Cookies;
use Vokuro\Infrastructure\Http\Csrf;
use Vokuro\Infrastructure\Mail\Mailer;
use Vokuro\Infrastructure\Repository\EmailConfirmationRepository;
use Vokuro\Infrastructure\Repository\FailedLoginRepository;
use Vokuro\Infrastructure\Repository\PasswordChangeRepository;
use Vokuro\Infrastructure\Repository\PermissionRepository;
use Vokuro\Infrastructure\Repository\ProfileRepository;
use Vokuro\Infrastructure\Repository\RememberTokenRepository;
use Vokuro\Infrastructure\Repository\ResetPasswordRepository;
use Vokuro\Infrastructure\Repository\SuccessLoginRepository;
use Vokuro\Infrastructure\Repository\UserRepository;
use Vokuro\Infrastructure\View\LayoutRenderer;
use Vokuro\Middleware\RememberMeLogin;
use Vokuro\Middleware\RequireLogin;
use Vokuro\Middleware\RequirePermission;
use Vokuro\Responder\AuthResponder;
use Vokuro\Responder\ErrorPageResponder;
use Vokuro\Responder\PrivateResponder;

/**
 * The entry point of the application. It loads the environment, registers the
 * ADR seams and everything this application adds on top of them.
 */
class AppFront extends AbstractHttpFront
{
    protected function getApplication(Container $container): ApplicationInterface
    {
        return (new Application($container))
            ->setBaseNamespace('Vokuro\\Action')
            ->secureWith(RequireLogin::class, '\\Users\\')
            ->secureWith(RequirePermission::class, '\\Users\\')
            ->secureWith(RequireLogin::class, '\\Profiles\\')
            ->secureWith(RequirePermission::class, '\\Profiles\\')
            ->secureWith(RequireLogin::class, '\\Permissions\\')
            ->secureWith(RequirePermission::class, '\\Permissions\\');
    }

    protected function loadEnvironment(Container $container): void
    {
        Dotenv::createUnsafeImmutable($this->projectRoot)->safeLoad();
    }

    /**
     * Registers this application's services on top of the ADR seams.
     *
     * The short aliases (`url`, `session`, `db`, `dispatcher`) are set purely
     * as an example of `setAlias()`. Nothing resolves a service by those
     * names - every dependency is autowired by its contract or class.
     */
    protected function registerProviders(Container $container): void
    {
        parent::registerProviders($container);

        /**
         * Templates build their links with it. Only the route name form of
         * `get()` needs a container, and nothing here uses that form.
         */
        $container->set(
            UrlInterface::class,
            function () {
                $url = new Url();
                $url->setBaseUri($this->env('APP_BASE_URI', '/'));

                return $url;
            }
        );
        $container->setAlias(UrlInterface::class, 'url');

        $container->set(
            ManagerInterface::class,
            function () {
                $session = new SessionManager();
                $session->setAdapter(
                    new Stream(
                        ['savePath' => $this->projectRoot . '/var/cache/session']
                    )
                );
                $session->start();

                return $session;
            }
        );
        $container->setAlias(ManagerInterface::class, 'session');

        /**
         * The session and the request are constructor arguments, so the CSRF
         * token works without a `Phalcon\Di\DiInterface`.
         */
        $container->set(
            Security::class,
            function ($container) {
                return new Security(
                    $container->get(ManagerInterface::class),
                    $container->get(AttributeRequest::class)
                );
            }
        );

        /**
         * The ADR provider leaves a null logger in place. Anything the error
         * responder catches goes here instead of nowhere.
         */
        $container->set(
            LoggerInterface::class,
            function () {
                return new Logger(
                    'vokuro',
                    [
                        'main' => new LogStream(
                            $this->projectRoot . '/var/logs/application.log'
                        )
                    ]
                );
            }
        );

        /**
         * The data mapper, not `Mvc\Model`: the ORM resolves its services
         * through a `Phalcon\Di\DiInterface` that an ADR application does not
         * have, while the data mapper needs no container at all.
         */
        $container->set(
            Connection::class,
            function () {
                return new Connection(
                    sprintf(
                        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                        $this->env('DB_HOST', 'mysql'),
                        (int) ($this->env('DB_PORT', 3306)),
                        $this->env('DB_NAME', 'vokuro_adr')
                    ),
                    $this->env('DB_USERNAME', 'root'),
                    $this->env('DB_PASSWORD', '')
                );
            }
        );
        $container->setAlias(Connection::class, 'db');

        /**
         * Domains depend on the repository contracts, so the storage behind
         * them can change without a domain noticing.
         */
        $container->bind(UserRepositoryInterface::class, UserRepository::class);
        $container->bind(
            ResetPasswordRepositoryInterface::class,
            ResetPasswordRepository::class
        );
        $container->bind(
            EmailConfirmationRepositoryInterface::class,
            EmailConfirmationRepository::class
        );
        $container->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $container->bind(FailedLoginRepositoryInterface::class, FailedLoginRepository::class);
        $container->bind(SuccessLoginRepositoryInterface::class, SuccessLoginRepository::class);
        $container->bind(PasswordChangeRepositoryInterface::class, PasswordChangeRepository::class);
        $container->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $container->bind(Authorization::class, Authorizer::class);

        /**
         * The remember-me check is global: it runs on every request before the
         * route middleware, so a returning visitor is signed in from the cookie
         * wherever they land.
         */
        $container->set(
            DispatcherContract::class,
            function ($container) {
                return new Dispatcher(
                    $container,
                    $container->get(EventsManagerContract::class),
                    [RememberMeLogin::class]
                );
            }
        );
        $container->setAlias(DispatcherContract::class, 'dispatcher');
        $container->bind(CookiesInterface::class, Cookies::class);
        $container->bind(CsrfInterface::class, Csrf::class);
        $container->bind(
            RememberTokenRepositoryInterface::class,
            RememberTokenRepository::class
        );

        /**
         * The ADR provider does not bind a renderer, so the application binds
         * its own. The view is deliberately left without registered engines:
         * registering one requires a `Phalcon\Di\DiInterface`, which the ADR
         * container is not, so templates are `.phtml`.
         */
        $this->registerRenderers($container);

        /**
         * The mailer renders through the e-mail layout, so a caller only ever
         * names a template.
         */
        $container->set(
            MailerInterface::class,
            function ($container) {
                $transport = new EsmtpTransport(
                    $this->env('MAIL_SMTP_SERVER', 'mailpit'),
                    (int) ($this->env('MAIL_SMTP_PORT', 1025))
                );
                $transport->setUsername($this->env('MAIL_SMTP_USERNAME', ''));
                $transport->setPassword($this->env('MAIL_SMTP_PASSWORD', ''));

                return new Mailer(
                    $this->newRenderer($container, 'layouts/emailTemplates'),
                    $this->env('MAIL_FROM_EMAIL', 'mail@vokuro.phalcon.io'),
                    $this->env('MAIL_FROM_NAME', 'Vokuro'),
                    $this->env('APP_PUBLIC_URL', 'localhost'),
                    $transport
                );
            }
        );
    }

    /**
     * Wrapper to getenv() and $_ENV
     *
     * @param string $key
     * @param mixed  $defaultValue
     * @return mixed
     */
    private function env(string $key, mixed $defaultValue = null): mixed
    {
        $value = getenv($key);
        if (false !== $value) {
            return $value;
        }

        return $_ENV[$key] ?? $defaultValue;
    }

    /**
     * Builds a view bound to the given layout, with the helpers every template
     * expects to find.
     */
    private function newRenderer(Container $container, string $layout): LayoutRenderer
    {
        $view = new Simple();
        $view->setViewsDir($this->projectRoot . '/resources/views/');
        $view->setVar('tag', $container->get(TagFactory::class));
        $view->setVar('url', $container->get(UrlInterface::class));
        $view->setVar('csrf', $container->get(CsrfInterface::class));

        return new LayoutRenderer(
            $view,
            $container->get(AttributeRequest::class),
            $container->get(ManagerInterface::class),
            $layout
        );
    }

    private function registerRenderers(Container $container): void
    {
        /**
         * The ADR provider does not bind a renderer, so the application binds
         * its own. The view is deliberately left without registered engines:
         * registering one requires a `Phalcon\Di\DiInterface`, which the ADR
         * container is not, so templates are `.phtml`.
         */
        $container->set(
            Renderer::class,
            function ($container) {
                return $this->newRenderer($container, 'layouts/public');
            }
        );

        /**
         * A browser gets an error page instead of a JSON body, and a
         * development environment gets the exception with it.
         */
        $container->set(
            ErrorResponder::class,
            function ($container) {
                return new ErrorResponder(
                    new ErrorPageResponder(
                        new ViewResponder(
                            $this->newRenderer($container, 'layouts/public'),
                            $container->get(StatusMapper::class)
                        ),
                        new JsonResponder()
                    ),
                    $container->get(LoggerInterface::class),
                    'development' === $this->env('APP_ENV', 'production')
                );
            }
        );

        /**
         * Pages behind the login use a different layout, and a renderer knows
         * only one, so they ask for this responder instead.
         */
        $container->set(
            AuthResponder::class,
            function ($container) {
                return new AuthResponder(
                    new ViewResponder(
                        $this->newRenderer($container, 'layouts/auth'),
                        $container->get(StatusMapper::class)
                    )
                );
            }
        );

        /**
         * The management area renders in the sidebar layout.
         */
        $container->set(
            PrivateResponder::class,
            function ($container) {
                return new PrivateResponder(
                    new ViewResponder(
                        $this->newRenderer($container, 'layouts/private'),
                        $container->get(StatusMapper::class)
                    )
                );
            }
        );
    }
}
