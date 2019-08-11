<?php

namespace SuperV\Platform\Domains\Drop\Contracts;

use Closure;

interface Drop
{
    public function getDropKey(): string;

    public function getFullKey(): string;

    public function getRepoIdentifier(): string;

    public function getRepoHandler(): string;

    public function getEntryValue();

    public function setEntryValue($value);

    public function updateEntryValue($value);

    public function getEntryId();

    public function setEntryId($entryId);

    public function onUpdateCallback(Closure $callback): Drop;
}

