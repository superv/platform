<?php

namespace SuperV\Platform\Domains\Database\Migrations;

use SuperV\Platform\Domains\Database\Schema;

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
}