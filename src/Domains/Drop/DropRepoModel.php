<?php

namespace SuperV\Platform\Domains\Drop;

use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Drop\Contracts\Drop;

class DropRepoModel extends Entry
{
    protected $table = 'sv_drop_repos';

    public function createDrop(array $attributes): Drop
    {
        return $this->drops()->create($attributes);
    }

    public function getDrop($dropKey): Drop
    {
        return $this->drops()->where('key', $dropKey)->first();
    }

    public function drops()
    {
        return $this->hasMany(DropModel::class, 'repo_id');
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getFullKey()
    {
        return $this->getNamespace().'.'.$this->getIdentifier();
    }

    public static function findWithFullKey($fullKey): DropRepoModel
    {
        [$namespace, $identifier] = explode('.', $fullKey);

        return static::query()->where('namespace', $namespace)
                     ->where('identifier', $identifier)
                     ->first();
    }
}
