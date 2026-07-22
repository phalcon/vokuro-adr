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

use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Domain\Collection\ProfileCollection;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Page;

/**
 * In-memory {@see ProfileRepository}.
 */
final class FakeProfileRepository implements ProfileRepository
{
    /** @var array<int, Profile> */
    public array $profiles = [];

    /** @var array<int, array<string, mixed>> */
    public array $added = [];

    /** @var array<int, array<string, mixed>> */
    public array $updated = [];

    /** @var array<int, int> */
    public array $deleted = [];

    private int $nextId = 1;

    public function seed(Profile $profile): self
    {
        $this->profiles[$profile->id] = $profile;
        $this->nextId = max($this->nextId, $profile->id + 1);

        return $this;
    }

    public function add(array $profile): int
    {
        $id                   = $this->nextId++;
        $this->added[$id]     = $profile;
        $this->profiles[$id]  = new Profile(
            $id,
            (string) ($profile['name'] ?? ''),
            'Y' === ($profile['active'] ?? 'N')
        );

        return $id;
    }

    public function delete(int $id): void
    {
        $this->deleted[] = $id;
        unset($this->profiles[$id]);
    }

    public function findById(int $id): ?Profile
    {
        return $this->profiles[$id] ?? null;
    }

    public function listForSelect(): array
    {
        $out = [];
        foreach ($this->profiles as $profile) {
            $out[$profile->id] = $profile->name;
        }

        return $out;
    }

    public function page(int $page, int $perPage, array $filters = []): Page
    {
        return new Page(new ProfileCollection(array_values($this->profiles)), $page, $page, count($this->profiles));
    }

    public function update(int $id, array $fields): void
    {
        $this->updated[$id] = $fields;
    }
}
