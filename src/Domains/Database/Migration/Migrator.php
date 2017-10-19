<?php

namespace SuperV\Platform\Domains\Database\Migration;

use Illuminate\Database\Migrations\Migrator as BaseMigrator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Droplet\Droplet;

class Migrator extends BaseMigrator
{
    use DispatchesJobs;

    /** @var Droplet */
    protected $droplet = null;

    /**
     * The migration repository.
     *
     * @var DatabaseMigrationRepository
     */
    protected $repository;

    /**
     * Run the migrations.
     *
     * @param array $paths
     * @param array $options
     *
     * @return array
     */
    public function run($paths = [], array $options = [])
    {
        $this->repository->setMigrator($this);

        return parent::run($paths, $options);
    }

    /**
     * Rolls all of the currently applied migrations back.
     *
     * This is a carbon copy of the Laravel method
     * except in the "!isset($files[$migration])" part.
     *
     * @param  array|string $paths
     * @param  bool         $pretend
     *
     * @return array
     */
    public function reset($paths = [], $pretend = false)
    {
        $this->repository->setMigrator($this);

        $this->notes = [];

        $rolledBack = [];

        $files = $this->getMigrationFiles($paths);

        // Next, we will reverse the migration list so we can run them back in the
        // correct order for resetting this database. This will allow us to get
        // the database back into its "empty" state ready for the migrations.
        $migrations = array_reverse($this->repository->getRan());

        $count = count($migrations);

        if ($count === 0) {
            $this->note('<info>Nothing to rollback.</info>');
        } else {
            $this->requireFiles($files);

            // Next we will run through all of the migrations and call the "down" method
            // which will reverse each migration in order. This will get the database
            // back to its original "empty" state and will be ready for migrations.
            foreach ($migrations as $migration) {

                /*
                 * This is the only adjustment to
                 * Laravel's method..
                 */
                if (! isset($files[$migration])) {
                    continue;
                }

                $rolledBack[] = $files[$migration];

                $this->runDown($files[$migration], (object)['migration' => $migration], $pretend);
            }
        }

        return $rolledBack;
    }

    /**
     * Rollback the last migration operation.
     *
     * @param  array|string $paths
     * @param  array        $options
     *
     * @return array
     */
    public function rollback($paths = [], array $options = [])
    {
        $this->repository->setMigrator($this);

        return parent::rollback($paths, $options);
    }

    /**
     * Run "up" a migration instance.
     *
     * @param  string $file
     * @param  int    $batch
     * @param  bool   $pretend
     *
     * @return void
     */
    protected function runUp($file, $batch, $pretend)
    {
        /**
         * Run our migrations first.
         *
         * @var Migration
         */
        $migration = $this->resolve($file);

        if ($migration instanceof Migration) {
             if ($droplet = $this->getDroplet()) {
                    $migration->setDroplet($droplet);
                }
//            $this->dispatch(new Migrate($migration));
         }


        parent::runUp($file, $batch, $pretend);
    }

    /**
     * Run "down" a migration instance.
     *
     * @param  string $file
     * @param  object $migration
     * @param  bool   $pretend
     *
     * @return void
     */
    protected function runDown($file, $migration, $pretend)
    {
        /**
         * Run our migrations first.
         *
         * @var Migration
         */
        $migration = $this->resolve($file);



        if ($migration instanceof Migration) {
            if ($droplet = $this->getDroplet()) {
                   $migration->setDroplet($droplet);
               }
//            $this->dispatch(new Reset($migration));
        }

        parent::runDown($file, $migration, $pretend);
    }

    public function resolve($file)
    {
        $migrationFile = new MigrationName($file);

        $migration = app($migrationFile->className());

        $migration->migration = $migrationFile->migration();

        return $migration;
    }

    public function clearDroplet()
    {
        $this->droplet = null;

        return $this;
    }

    /**
     * @return Droplet
     */
    public function getDroplet()
    {
        return $this->droplet;
    }

    /**
     * @param Droplet $droplet
     *
     * @return Migrator
     */
    public function setDroplet(Droplet $droplet)
    {
        $this->droplet = $droplet;

        return $this;
    }
}
