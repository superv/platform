<?php

namespace SuperV\Platform\Domains\Drop;

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
    }
}
