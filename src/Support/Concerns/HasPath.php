<?php

namespace SuperV\Platform\Support\Concerns;

trait HasPath
{
    protected function relativePath(): string
    {
        return ltrim(str_replace(base_path(), '', $this->path), '/');
    }

    protected function realPath()
    {
        return starts_with($this->path, '/') ? $this->path : base_path($this->path);
    }
}