<?php

namespace SuperV\Platform\Domains\Database\Model\Contracts;

interface EntryContract extends Watcher
{
    public function getId();

    public function getTable();

    public function toArray();

    public function getMorphClass();

    public function getForeignKey();

    public function setKeyName($name);

    public function relationLoaded($key);

    public function load($relations);

    public function getRelation($key);

    public function wasRecentlyCreated(): bool;

    public function update(array $attributes = []);
}