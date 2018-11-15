<?php

namespace SuperV\Platform\Domains\Database\Migrations;

use Current;
use Illuminate\Database\Migrations\Migrator as BaseMigrator;

/**
 * Class Migrator
 *
 * @package SuperV\Platform\Packs\Database\Migrations
 * @property \SuperV\Platform\Domains\Database\Migrations\DatabaseMigrationRepository $repository
 */
class Migrator extends BaseMigrator
{
    protected $scope;

    public function paths()
    {
        $paths = parent::paths();
        if ($this->scope && $path = Scopes::path($this->scope)) {
            $paths[] = $path;
        }

        return $paths;
    }

    public function getMigrationFiles($paths)
    {
        return parent::getMigrationFiles($paths);
    }

    protected function runUp($file, $batch, $pretend)
    {
        if ($scope = Scopes::key(pathinfo($file, PATHINFO_DIRNAME))) {
            $this->setScope($scope);
        }

        parent::runUp($file, $batch, $pretend);
    }

    protected function runMigration($migration, $method)
    {
        $this->repository->setMigration($migration);
        if ($migration instanceof InScope) {
            $migration->setScope($this->scope);

            Current::setMigrationScope($this->scope);
        } else {
            Current::setMigrationScope(null);
        }
        parent::runMigration($migration, $method);
    }

    public function setScope($scope)
    {
        $this->repository->setScope($scope);

        $this->scope = $scope;

        return $this;
    }
}