<?php

namespace SuperV\Platform\Packs\Database\Migrations;

class DatabaseMigrationRepository extends \Illuminate\Database\Migrations\DatabaseMigrationRepository
{
    protected $migration;

    protected $scope;

    public function getMigrations($steps)
    {
        return $this->filterScope(parent::getMigrations($steps));
    }

    public function getLast()
    {
        return $this->filterScope(parent::getLast());
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
     * @param $migrations
     *
     * @return array
     */
    public function filterScope($migrations)
    {
        if (! $this->scope) {
            return $migrations;
        }

        $migrations = collect($migrations)->filter(
            function ($migration) {
                return $migration->scope === $this->scope;
            })->values()->toArray();

        $this->scope = null;

        return $migrations;
    }
}