<?php

namespace SuperV\Platform\Domains\Database\Model;

interface BelongsToEntry
{
    public function getOwnerEntry(): ?Entry;

    public function setOwnerEntry(Entry $entry);
}