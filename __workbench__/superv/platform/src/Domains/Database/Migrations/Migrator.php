<?php

namespace SuperV\Platform\Domains\Database\Migrations;

use Illuminate\Database\Migrations\Migrator as BaseMigrator;

/**
 * Class Migrator
 * @package SuperV\Platform\Packs\Database\Migrations
 * @property \SuperV\Platform\Domains\Database\Migrations\DatabaseMigrationRepository $repository
 */
class Migrator extends BaseMigrator
{
    protected $scope;

    public function setScope($scope)
    {
        $this->repository->setScope($scope);

        $this->scope = $scope;

        return $this;
    }

    public function run($paths = [], array $options = [])
    {
        return parent::run($paths, $options);
    }

    /**
     * Run an array of migrations.
     *
     * @param  array  $migrations
     * @param  array  $options
     * @return void
     */
    public function runPending(array $migrations, array $options = [])
    {
        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($migrations) == 0) {
            $this->note('<info>Nothing to migrate.</info>');

            return;
        }

        // Next, we will get the next batch number for the migrations so we can insert
        // correct batch number in the database migrations repository when we store
        // each migration's execution. We will also extract a few of the options.
        $batch = $this->repository->getNextBatchNumber();

        $pretend = $options['pretend'] ?? false;

        $step = $options['step'] ?? false;

        // Once we have the array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
        foreach ($migrations as $file) {
            $migration = $this->resolve($name = $this->getMigrationName($file));
            if ($this->scope) {
                if (!$migration instanceof Migration || $migration->scope() !== $this->scope) {
                    continue;
                }
            }
            $this->runUp($file, $batch, $pretend);

            if ($step) {
                $batch++;
            }
        }
    }


    protected function runUp($file, $batch, $pretend)
    {
        $this->repository->setMigration($this->resolve(
            $name = $this->getMigrationName($file)
        ));

        parent::runUp($file, $batch, $pretend);
    }
}