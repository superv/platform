<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\File;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;

class Blueprint extends FieldBlueprint
{
    protected $disk = 'local';

    protected $path = '/';

    protected $public = false;

    protected $allowedTypes = [];

    public function mergeConfig(): array
    {
        return [
            'disk'          => $this->disk,
            'path'          => $this->path,
            'public'        => $this->public,
            'allowed_types' => $this->allowedTypes,
        ];
    }

    public function disk(string $disk): Blueprint
    {
        $this->disk = $disk;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function path(string $path): Blueprint
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function public(bool $public = true): Blueprint
    {
        $this->public = $public;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function allowedTypes(): Blueprint
    {
        $this->allowedTypes = func_get_args();

        return $this;
    }

    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }
}