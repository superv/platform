<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\DatabaseManager;

class SchemaService
{
    /**
     * Database connection name;
     *
     * @var string
     */
    protected $connectionName;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $manager;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function dropTable($table)
    {
        $this->getSchemaManager()->dropTable($table);
    }

    public function createTable(Table $table)
    {
        $this->getSchemaManager()->createTable($table);
    }

    public function tableExists($table)
    {
        return $this->manager->tablesExist([$table]);
    }

    public function getIndexes($table)
    {
        return $this->getSchemaManager()->listTableIndexes($table);
    }

    public function getColumn($table, $column): TableColumn
    {
        return $this->getTableColumns($table)->get($column);
    }

    public function getTableColumns($table)
    {
        return collect($this->getSchemaManager()->listTableColumns($table))
            ->map(function (Column $column) {
                return new TableColumn($column);
            })
            ->keyBy(function (TableColumn $column) {
                return $column->getName();
            });
    }

    public function setConnection(string $connectionName = null): SchemaService
    {
        $this->connectionName = $connectionName;

        $this->prepareConnection();

        return $this;
    }

    public function getConnection(): \Doctrine\DBAL\Connection
    {
        return $this->connection;
    }

    public function getSchemaManager(): \Doctrine\DBAL\Schema\AbstractSchemaManager
    {
        return $this->getConnection()->getSchemaManager();
    }

    protected function prepareConnection()
    {
        $this->connection = $this->db->connection($this->connectionName)->getDoctrineConnection();

        $this->manager = $this->getConnection()->getSchemaManager();
    }

    /** *
     * @param string $connection
     * @return static
     */
    public static function resolve(string $connection = null)
    {
        /** @var \SuperV\Platform\Domains\Database\Schema\SchemaService $instance */
        $instance = app(static::class);

        return $instance->setConnection($connection);
    }
}