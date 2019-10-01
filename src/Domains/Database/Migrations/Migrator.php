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

        $migration = $this->resolve(
            $name = $this->getMigrationName($file)
        );

        /**
         * Skip normal migrations when --namespace is provided..
         */
        if ($this->namespace && ! $migration instanceof PlatformMigration) {
            return;
        }

        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }

        $this->note("<comment>Migrating:</comment> {$name}");

        $startTime = microtime(true);

        $scope = $this->setMigrationScope($migration);

        if ($scope && ! Platform::isInstalled()) {
            $this->note("<info>Skipped:</info>  {$name}");
        } else {
            $this->runMigration($migration, 'up');

            $this->repository->log($name, $batch);

            $runTime = round(microtime(true) - $startTime, 2);

            $this->note("<info>Migrated:</info>  {$name} ({$runTime} seconds)");
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
            if (! $migration->getNamespace()) {
                $scope = $this->namespace;
            } else {
                $scope = $migration->getNamespace() ?? 'app';
            }

            $migration->setNamespace($scope);
            $this->setNamespace($scope);
        }

        Current::setMigrationScope($scope);

        return $scope;
    }
}
