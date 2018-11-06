<?php

namespace SuperV\Platform\Domains\Database\Migrations;

use Closure;
use SuperV\Platform\Domains\Database\Schema\Schema;

class Migration extends \Illuminate\Database\Migrations\Migration implements InScope
{
    protected $scope;

    public function getScope()
    {
        return $this->scope;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function schema()
    {
        return new Schema();
    }

    public function create($table, Closure $callback)
    {
        return $this->schema()->create($table, $callback);
    }

    public function table($table, Closure $callback)
    {
        return $this->schema()->table($table, $callback);
    }

    public function drop($table)
    {
        return $this->schema()->drop($table);
    }

    public function dropIfExists($table)
    {
        return $this->schema()->dropIfExists($table);
    }
}