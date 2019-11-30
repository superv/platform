<?php

namespace SuperV\Platform\Domains\Database\Schema;

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

    public function getIndexes($table)
    {
        return $this->getSchemaManager()->listTableIndexes($table);
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

    protected function prepareConnection()
    {
        $this->connection = $this->db->connection($this->connectionName)->getDoctrineConnection();
    }

    public function getSchemaManager(): \Doctrine\DBAL\Schema\AbstractSchemaManager
    {
        return $this->getConnection()->getSchemaManager();
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

//    public function getDatabaseTables_xxxxxxx($connection = null): Collection
//    {
//        return collect($this->getSchemaManager()->listTableNames());
//    }
//
//    public function getTable_xxxxxxx($tableName)
//    {
//        return $this->getDatabaseTables()->first(function ($table) use ($tableName) {
//            return $tableName === $table;
//        });
//    }
//
//    public function formatSingular_xxxxxxx($table)
//    {
//        $replace = camel_case($table);
//
//        return str_singular(ucwords($replace));
//    }
//
//    public function getTableColumns_xxxxxxx($table, $connection = null)
//    {
//        return collect($this->db->connection($connection)->getDoctrineSchemaManager()->listTableColumns($table))
//            ->map(function (Column $column) use ($table, $connection) {
//                $columnType = $column->getType()->getName();
//                $mapper = ColumnFieldMapper::for($columnType)->map();
//
//                return [
//                    'id'         => $column->getName(),
//                    'label'      => str_unslug($column->getName()),
//                    'type'       => $columnType,
//                    'field_type' => $mapper->getFieldType(),
//                    'rules'      => $mapper->getRules(),
//                    'config'     => $mapper->getConfig(),
//                    'nullable'   => ! $column->getNotnull(),
//                ];
//            })->values();
//    }

}