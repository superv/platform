<?php

namespace SuperV\Platform\Domains\Drop;

use SuperV\Platform\Domains\Drop\Contracts\Drop;

class Factory
{
    protected $dropKey;

    protected $repoKey;

    public function create($dropKey): Drop
    {
        /** @var \SuperV\Platform\Domains\Drop\Contracts\Drop $drop */
        $drop = sv_resource('sv_drops')->create([
            'key'      => $dropKey,
            'repo_key' => $this->repoKey,
        ]);

        return $drop;
    }

    public function dropKey($dropKey): Factory
    {
        $this->dropKey = $dropKey;

        return $this;
    }

    public function setRepo(RepoHandler $repo): Factory
    {
        $this->repo = $repo;

        return $this;
    }

    public function repoKey($repoKey): Factory
    {
        $this->repoKey = $repoKey;

        return $this;
    }

    public static function repo($repoKey): Factory
    {
        return (new static)->repoKey($repoKey);
    }
}
