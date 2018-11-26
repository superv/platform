<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class EntryResource extends Resource
{
    protected $entry;

    public function __construct(EntryContract $entry, array $attributes = [])
    {
        $this->entry = $entry;
        parent::__construct($attributes);
    }

    public function getEntry(): EntryContract
    {
        return $this->entry;
    }

    public static function make(EntryContract $entry)
    {
        return ResourceFactory::makeWithEntry($entry);

    }
}