<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface EntryRepositoryInterface
{
    public function getEntry(string $identifier, int $id = null): ?EntryContract;

    public function create(string $identifier, array $attributes = []);

    public function update(string $identifier, array $attributes = []);
}
