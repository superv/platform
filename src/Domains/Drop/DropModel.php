<?php

namespace SuperV\Platform\Domains\Drop;

use SuperV\Platform\Domains\Database\Model\Model;
use SuperV\Platform\Domains\Drop\Contracts\Drop;

class DropModel extends Model implements Drop
{
    private $entryValue;

    private $entryId;

    protected $table = 'sv_drops';

    protected $guarded = [];

    public $timestamps = false;

    public function repo()
    {
        return $this->belongsTo(DropRepoModel::class, 'repo_id');
    }

    public function getId()
    {
        return $this->getKey();
    }

    public function wasRecentlyCreated(): bool
    {
        return $this->wasRecentlyCreated;
    }

    public function getDropKey(): string
    {
        return $this->getAttribute('key');
    }

    public function getRepoIdentifier(): string
    {
        return $this->repo->getIdentifier();
    }

    public function getRepoHandler(): string
    {
        return $this->repo->getHandler();
    }

    public function getEntryValue()
    {
        return $this->entryValue;
    }

    public function setEntryValue($value)
    {
        $this->entryValue = $value;
    }

    public function getEntryId()
    {
        return $this->entryId;
    }

    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;
    }
}
