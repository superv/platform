<?php

namespace SuperV\Platform\Domains\Database\Migrations;

class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    protected $scope;

    public function stubPath()
    {
        return \Platform::fullPath('resources/stubs');
    }

    /**
     * @param mixed $scope
     *
     * @return MigrationCreator
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    protected function populateStub($name, $stub, $table)
    {
        $stub = parent::populateStub($name, $stub, $table);

        $scope = $this->scope ? "protected \$scope = '{$this->scope}';" : "";

        return str_replace('{scope}', $scope, $stub);
    }
}