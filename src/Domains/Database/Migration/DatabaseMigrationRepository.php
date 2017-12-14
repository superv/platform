<?php

namespace SuperV\Platform\Domains\Database\Migration;

use Illuminate\Database\Migrations\DatabaseMigrationRepository as BaseDatabaseMigrationRepository;

class DatabaseMigrationRepository extends BaseDatabaseMigrationRepository
{
    /** @var Migrator */
    protected $migrator = null;

    protected function tableDroplet()
    {
        $table = $this->table();

        if ($droplet = $this->migrator->getDroplet()) {
            $table->where('droplet', $droplet->getSlug());
        }

        return $table;
    }

    public function getRan()
    {
        return $this->tableDroplet()
                    ->orderBy('batch', 'asc')
                    ->orderBy('migration', 'asc')
                    ->pluck('migration')->all();
    }

    public function getLast()
    {
//        $query = $this->table()->where('batch', $this->getLastBatchNumber());
//        if ($droplet = $this->migrator->getDroplet()) {
//            $query->where('droplet', $droplet->getSlug());
//        }
//
//        return $query->orderBy('migration', 'desc')->get()->all();

        $query = $this->tableDroplet()->where('batch', $this->getLastBatchNumber());

        return $query->orderBy('migration', 'desc')->get()->all();
    }

    public function log($file, $batch)
    {
        $record = ['migration' => $file, 'batch' => $batch];

        if ($droplet = $this->migrator->getDroplet()) {
            array_set($record, 'droplet', $droplet->getSlug());
        }

        $this->table()->insert($record);
    }

    public function delete($migration)
    {
        $this->tableDroplet()->where('migration', $migration->migration)->delete();
    }

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber()
    {
        return $this->tableDroplet()->max('batch');
    }

    /**
     * Set the migrator.
     *
     * @param Migrator $migrator
     *
     * @return $this
     */
    public function setMigrator(Migrator $migrator)
    {
        $this->migrator = $migrator;

        return $this;
    }

    public function createRepository()
    {
        parent::createRepository();

        $schema = $this->getConnection()->getSchemaBuilder();
        $schema->table($this->table, function ($table) {
            $table->string('droplet')->nullable();
        });
    }
}
