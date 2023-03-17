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

        return str_replace('{namespace}', $this->namespace, $stub);
    }

    public function setNamespace($namespace): void
    {
        $this->namespace = $namespace;
    }
}