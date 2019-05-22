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
    protected $addon;

    public function paths()
    {
        $paths = parent::paths();
        if ($this->addon && $path = Scopes::path($this->addon)) {
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
            $this->setAddon($addon);
        }

        parent::runUp($file, $batch, $pretend);
    }

    protected function runMigration($migration, $method)
    {
        $this->repository->setMigration($migration);
        if ($migration instanceof AddonMigration) {
            $migration->setAddon($this->addon);

            Current::setMigrationScope($this->addon);
        } else {
            Current::setMigrationScope(null);
        }
        parent::runMigration($migration, $method);
    }

    public function setAddon($addon)
    {
        $this->repository->setAddon($addon);

        $this->addon = $addon;

        return $this;
    }
}