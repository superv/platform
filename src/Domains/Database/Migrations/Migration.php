<?php

namespace SuperV\Platform\Domains\Database\Migrations;

use Closure;
use SuperV\Platform\Domains\Database\Schema\Schema;

class Migration extends \Illuminate\Database\Migrations\Migration implements PlatformMigration
{
    protected $namespace;

    public function connection($connection)
    {
        return Schema::connection($connection);
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
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

    public function run($table, Closure $callback)
    {
        return $this->schema()->run($table, $callback);
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
