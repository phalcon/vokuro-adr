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

namespace Vokuro\Tests\Support\Fake;

use Phalcon\Session\ManagerInterface;
use SessionHandlerInterface;

use const PHP_SESSION_ACTIVE;
use const PHP_SESSION_NONE;

/**
 * An array-backed {@see ManagerInterface}. The real manager encrypts through
 * the container, which an ADR application does not have; this keeps the same
 * get/set/has/remove surface without any of that machinery.
 */
final class FakeSession implements ManagerInterface
{
    private ?SessionHandlerInterface $adapter = null;

    /** @var array<string, mixed> */
    private array $data = [];

    private string $id = 'fake-session-id';

    private string $name = 'fake';

    /** @var array<string, mixed> */
    private array $options = [];

    private bool $started = false;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function __unset(string $key): void
    {
        $this->remove($key);
    }

    public function destroy(): void
    {
        $this->data    = [];
        $this->started = false;
    }

    public function exists(): bool
    {
        return $this->started;
    }

    public function get(string $key, $defaultValue = null, bool $remove = false): mixed
    {
        $value = $this->data[$key] ?? $defaultValue;

        if (true === $remove) {
            unset($this->data[$key]);
        }

        return $value;
    }

    public function getAdapter(): ?SessionHandlerInterface
    {
        return $this->adapter;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function regenerateId(bool $deleteOldSession = true): ManagerInterface
    {
        return $this;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function setAdapter(SessionHandlerInterface $adapter): ManagerInterface
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function setId(string $sessionId): ManagerInterface
    {
        $this->id = $sessionId;

        return $this;
    }

    public function setName(string $name): ManagerInterface
    {
        $this->name = $name;

        return $this;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function start(): bool
    {
        $this->started = true;

        return true;
    }

    public function status(): int
    {
        return $this->started ? PHP_SESSION_ACTIVE : PHP_SESSION_NONE;
    }
}
