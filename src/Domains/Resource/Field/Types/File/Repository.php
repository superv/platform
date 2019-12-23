<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\File;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

class Repository
{
    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $owner;

    public function withLabel(string $label)
    {
        return FileType::getMedia($this->owner, $label);
    }

    public function setOwner(EntryContract $owner): Repository
    {
        $this->owner = $owner;

        return $this;
    }
}