<?php

namespace SuperV\Platform\Domains\Drop;

use SuperV\Platform\Domains\Drop\Contracts\Drop;

class Drops
{
    protected $drops = [];

    /**
     * @var \SuperV\Platform\Domains\Drop\DropRepoModel[]
     */
    protected $repos;

    /**
     * @var array
     */
    protected $entries;

    public function __construct(array $entries)
    {
        $this->entries = $entries;

        foreach ($entries as $fullKey => $entry) {
            $this->repos[$fullKey] = DropRepoModel::findWithFullKey($fullKey);
        }
    }

    public function resolve($keys)
    {
        foreach ($keys as $fullKey) {
            [$repoKey, $dropKey] = explode('::', $fullKey);

            $drop = $this->repos[$repoKey]->getDrop($dropKey);

            $value = array_get($this->entries[$repoKey], $drop->getDropKey());

            $drop->setEntryValue($value);
            $drop->setEntryId($this->entries[$repoKey]['id']);

            $this->drops[$fullKey] = $drop;
        }

        return $this;
    }

    public function add(Drop $drop)
    {
        $this->drops[$drop->getRepoIdentifier().'::'.$drop->getDropKey()] = $drop;

        return $this;
    }

    public function get($fullKey)
    {
        return $this->drops[$fullKey];
    }

    public static function make($entries)
    {
        return new static($entries);
    }
}
