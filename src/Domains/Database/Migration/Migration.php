<?php

namespace SuperV\Platform\Domains\Database\Migration;

use Illuminate\Database\Schema\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Migration extends \Illuminate\Database\Migrations\Migration
{

    use DispatchesJobs;


    protected $droplet;

    protected $namespace;


    /**
     * Return the schema builder.
     *
     * @return Builder
     */
    public function schema()
    {
        return app('db')->connection()->getSchemaBuilder();
    }


    /**
     * Migrate
     */
    public function up()
    {
    }

    /**
     * Rollback
     */
    public function down()
    {
    }

    /**
     * @return mixed
     */
    public function getDroplet()
    {
        return $this->droplet;
    }

    /**
     * @param mixed $droplet
     *
     * @return Migration
     */
    public function setDroplet($droplet)
    {
        $this->droplet = $droplet;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     *
     * @return Migration
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }



}
