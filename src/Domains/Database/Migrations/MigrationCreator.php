<?php

namespace SuperV\Platform\Domains\Database\Migrations;

class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    protected $namespace;

    public function stubPath()
    {
        return \Platform::fullPath('resources/stubs');
    }

    protected function populateStub($name, $stub, $table)
    {
        $stub = parent::populateStub($name, $stub, $table);

        $addon = $this->addon ? "protected \$addon = '{$this->addon}';" : "";

        return str_replace('{addon}', $addon, $stub);
    }

    public function setNamespace($namespace): void
    {
        $this->namespace = $namespace;
    }
}