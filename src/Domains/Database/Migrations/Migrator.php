<?php

namespace SuperV\Platform\Domains\Database\Migrations;

use Current;
use Illuminate\Database\Migrations\Migrator as BaseMigrator;
use Platform;

/**
 * Class Migrator
 *
 * @package SuperV\Platform\Packs\Database\Migrations
 * @property \SuperV\Platform\Domains\Database\Migrations\DatabaseMigrationRepository $repository
 */
class Migrator extends BaseMigrator
{
    protected $namespace;

    public function paths()
    {
        $paths = parent::paths();
        if ($this->namespace && $path = Scopes::path($this->namespace)) {
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
        if ($addon = Scopes::key(pathinfo($file, PATHINFO_DIRNAME))) {
            $this->setNamespace($addon);
        }

//        parent::runUp($file, $batch, $pretend);

        $migration = $this->resolve(
            $name = $this->getMigrationName($file)
        );

        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }

        $this->note("<comment>Migrating:</comment> {$name}");

        $scope = $this->setMigrationScope($migration);
//        if ($migration instanceof PlatformMigration) {
//            $this->note("<info>$scope</info>");
//            $this->note("<info>{$migration->getNamespace()}</info>");
//        }

        if ($scope && ! Platform::isInstalled()) {
            $this->note("<info>Skipped:</info>  {$name}");
        } else {
            $this->runMigration($migration, 'up');

            $this->repository->log($name, $batch);

            $this->note("<info>Migrated:</info>  {$name}");
        }
    }

    protected function runMigration($migration, $method)
    {
        $this->repository->setMigration($migration);

        parent::runMigration($migration, $method);
    }

    public function setNamespace($namespace)
    {
        $this->repository->setNamespace($namespace);

        $this->namespace = $namespace;

        return $this;
    }

    protected function setMigrationScope($migration)
    {
        $scope = null;

        if ($migration instanceof PlatformMigration) {
            if ($this->namespace) {
                if (! $migration->getNamespace()) {
                    $migration->setNamespace($this->namespace);
                    $scope = $this->namespace;
                }
            } else {
                $scope = $migration->getNamespace();
            }
        }
        Current::setMigrationScope($scope);

        return $scope;
    }
}
