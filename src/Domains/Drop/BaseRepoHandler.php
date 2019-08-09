<?php

namespace SuperV\Platform\Domains\Drop;

use SuperV\Platform\Domains\Drop\Contracts\Drop;
use SuperV\Platform\Domains\Drop\Contracts\RepoHandler;

class BaseRepoHandler implements RepoHandler
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $identifier;

    public function __construct(string $namespace, string $identifier)
    {
        $this->namespace = $namespace;
        $this->identifier = $identifier;
    }

    public function resolve($data)
    {
        $drops = sv_resource('sv_drop_repos')->newQuery()->where('namespace', $this->key)->get();

        $theDrops = new Drops();
        /** @var Drop $drop */
        foreach ($drops as $drop) {
            $value = array_get($data, $drop->getDropKey());

            $drop->setEntryValue($value);
            $drop->setEntryId($data['id']);

            $theDrops->add($drop);
        }

        return $theDrops;
    }
}
