<?php

namespace SuperV\Platform\Domains\Database\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

class Model extends Eloquent implements EntryContract
{
    protected $guarded = [];

    public $timestamps = false;

    public function getId()
    {
        return $this->getKey();
    }

    public function wasRecentlyCreated(): bool
    {
        return $this->wasRecentlyCreated;
    }
}
