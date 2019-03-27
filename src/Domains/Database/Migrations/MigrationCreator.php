<?php

namespace SuperV\Platform\Domains\Database\Migrations;

class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    protected $addon;

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

    /**
     * @param mixed $addon
     * @return MigrationCreator
     */
    public function setAddon($addon)
    {
        $this->addon = $addon;

        return $this;
    }
}