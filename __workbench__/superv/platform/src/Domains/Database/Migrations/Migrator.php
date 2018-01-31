<?php

namespace SuperV\Platform\Domains\Database\Migrations;

use Illuminate\Database\Migrations\Migrator as BaseMigrator;

/**
 * Class Migrator
 * @package SuperV\Platform\Domains\Database\Migrations
 * @property \SuperV\Platform\Domains\Database\Migrations\DatabaseMigrationRepository $repository
 */
class Migrator extends BaseMigrator
{
    public function setScope($scope)
    {
        $this->repository->setScope($scope);

        return $this;
    }

    public function run($paths = [], array $options = [])
    {
        return parent::run($paths, $options);
    }

    protected function runUp($file, $batch, $pretend)
    {
        $this->repository->setMigration($this->resolve(
            $name = $this->getMigrationName($file)
        ));

        parent::runUp($file, $batch, $pretend);
    }
}