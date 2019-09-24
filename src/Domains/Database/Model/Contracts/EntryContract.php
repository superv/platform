<?php

namespace SuperV\Platform\Domains\Database\Model\Contracts;

use SuperV\Platform\Domains\Resource\Model\EntryRouter;

interface EntryContract
{
    public function getId();

    public function getTable();

    public function toArray();

    public function getMorphClass();

    public function getForeignKey();

    public function getResourceIdentifier();

    public function getConnection();

    public function setKeyName($name);

    public function relationLoaded($key);

    public function load($relations);

    public function getRelation($key);

    public function wasRecentlyCreated(): bool;

    public function update(array $attributes = []);

    public function setAttribute($key, $value);

    public function getAttribute($key);

    public function getAttributes();

    public function save();

    public function exists();

    public function delete();

    public function fresh();

    public function router(): EntryRouter;
}
