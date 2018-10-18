<?php

namespace SuperV\Platform\Domains\Database\Migrations;

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
}