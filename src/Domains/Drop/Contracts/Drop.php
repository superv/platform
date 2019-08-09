<?php

namespace SuperV\Platform\Domains\Drop\Contracts;

interface Drop
{
    public function getDropKey(): string;

    public function getRepoIdentifier(): string;

    public function getRepoHandler(): string;

    public function getEntryValue();

    public function setEntryValue($value);

    public function getEntryId();

    public function setEntryId($entryId);
}

