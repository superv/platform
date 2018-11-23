<?php

namespace SuperV\Platform\Support\Composer;

class Tokens
{
    /** @var array  */
    protected $tokens;

    public function __construct($tokens)
    {
        $this->tokens = wrap_array($tokens);
    }

    public function merge(array $tokens): self
    {
        $this->tokens = array_merge($this->tokens, $tokens);

        return $this;
    }

    public function get()
    {
        return $this->tokens;
    }
}