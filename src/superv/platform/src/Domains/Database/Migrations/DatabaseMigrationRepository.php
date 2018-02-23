<?php

namespace SuperV\Platform\Domains\Database\Migrations;

class DatabaseMigrationRepository extends \Illuminate\Database\Migrations\DatabaseMigrationRepository
{
    protected $migration;

    protected $scope;

    public function getRan()
    {
        return $this->filterScope($this->table())
                    ->orderBy('batch', 'asc')
                    ->orderBy('migration', 'asc')
                    ->pluck('migration')->all();
    }

    public function getMigrations($steps)
    {
        $query = $this->table()->where('batch', '>=', '1');

        return $this->filterScope($query)
                    ->orderBy('batch', 'desc')
                    ->orderBy('migration', 'desc')
                    ->take($steps)->get()->all();
    }

    public function getLast()
    {
        $query = $this->table()->where('batch', $this->getLastBatchNumber());

        return $this->filterScope($query)->orderBy('migration', 'desc')->get()->all();
    }

    public function createRepository()
    {
        parent::createRepository();

        $schema = $this->getConnection()->getSchemaBuilder();
        $schema->table($this->table, function ($table) {
            $table->string('scope')->nullable();
        });
    }

    public function log($file, $batch)
    {
        $record = [
            'migration' => $file,
            'batch'     => $batch,
        ];

        if ($this->migration) {
            if ($this->migration instanceof Migration) {
                array_set($record, 'scope', $this->migration->scope());
            }
        }

        $this->table()->insert($record);
    }

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber()
    {
        return $this->filterScope($this->table())->max('batch');
    }

    /**
     * @param mixed $migration
     */
    public function setMigration($migration)
    {
        $this->migration = $migration;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function filterScope($query)
    {
        if ($this->scope) {
            $query->where('scope', $this->scope);
        }

        return $query;
    }
}