<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Closure;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;

class SchemaService
{
    private $db;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function dropTable($table)
    {
        $this->db->connection()->getDoctrineSchemaManager()->dropTable($table);
    }

    public function createTable(Table $table)
    {
        $this->db->connection()->getDoctrineSchemaManager()->createTable($table);
    }

    public function getIndexes($table)
    {
        return $this->db->connection()->getDoctrineSchemaManager()->listTableIndexes($table);
    }

    public function createTablexxxxxxxx($tableName, array $primaryKeys, Closure $callback = null)
    {
        $table = new Table($tableName);
        foreach ($primaryKeys as $primaryKey) {
            if ($primaryKey['type'] === 'integer') {
                $options = ['unsigned' => true, 'autoincrement' => $primaryKey['autoincrement'] ?? false];
            }
            $table->addColumn($primaryKey['name'], $primaryKey['type'], $options ?? []);
        }

        if ($callback) {
            $callback($table);
        }

        $this->db->connection()->getDoctrineSchemaManager()->createTable($table);
    }

    public function getDatabaseTables($connection = null): Collection
    {
        return collect($this->db->connection($connection)->getDoctrineSchemaManager()->listTableNames());

        return collect($this->db->connection($connection)->getDoctrineSchemaManager()->listTableNames())
            ->map(function (string $table) {
                return [
                    'id'       => $table,
                    'table'    => $table,
                    'singular' => $this->formatSingular($table),
                ];
            })->keyBy('table');
    }

    public function getTable($tableName)
    {
        return $this->getDatabaseTables()->first(function ($table) use ($tableName) {
            return $tableName === $table;
        });
    }

    public function formatSingular($table)
    {
        $replace = camel_case($table);

        return str_singular(ucwords($replace));
    }

    public function getTableColumns($table, $connection = null)
    {
        return collect($this->db->connection($connection)->getDoctrineSchemaManager()->listTableColumns($table))
            ->map(function (Column $column) use ($table, $connection) {
                $columnType = $column->getType()->getName();
                $mapper = ColumnFieldMapper::for($columnType)->map();

                return [
                    'id'         => $column->getName(),
                    'label'      => str_unslug($column->getName()),
                    'type'       => $columnType,
                    'field_type' => $mapper->getFieldType(),
                    'rules'      => $mapper->getRules(),
                    'config'     => $mapper->getConfig(),
                    'nullable'   => ! $column->getNotnull(),
                ];
            })->values();
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}