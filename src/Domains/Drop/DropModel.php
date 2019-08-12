<?php

namespace SuperV\Platform\Domains\Drop;

use Closure;
use SuperV\Platform\Domains\Database\Model\Model;
use SuperV\Platform\Domains\Drop\Contracts\Drop;

class DropModel extends Model implements Drop
{
    private $entryValue;

    private $entryId;

    private $onUpdateCallback;

    protected $table = 'sv_drops';

    protected $guarded = [];

    public $timestamps = false;

    public function repo()
    {
        return $this->belongsTo(DropRepoModel::class, 'repo_id');
    }

    public function getRepo(): DropRepoModel
    {
        return $this->repo;
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

    public function getFullKey(): string
    {
        return $this->getRepo()->getFullKey().'::'.$this->getDropKey();
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

    public function updateEntryValue($value)
    {
        if ($this->onUpdateCallback) {
            call_user_func_array($this->onUpdateCallback, [$this->getRepo()->getFullKey(), $value]);
        }
        $this->setEntryValue($value);
    }

    public function onUpdateCallback(Closure $callback): Drop
    {
        $this->onUpdateCallback = $callback;

        return $this;
    }
}
