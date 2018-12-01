<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\DatabaseManager;

class SchemaService
{
    private $db;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function getDatabaseTables($connection = null)
    {
        return collect($this->db->connection($connection)->getDoctrineSchemaManager()->listTableNames())
            ->map(function (string $table) {
                return [
                    'table'    => $table,
                    'singular' => $this->formatSingular($table),
                ];
            });
    }

    public function formatSingular($tbl)
    {
        $replace = camel_case($tbl);

        return str_singular(ucwords($replace));
    }

    /**
     * [getTableColumns description].
     *
     * @param  [type] $table      [description]
     * @param  [type] $connection [description]
     * @return [type]             [description]
     */
    public function getTableColumns($table, $connection = null)
    {
        return collect($this->db->connection($connection)->getDoctrineSchemaManager()->listTableColumns($table))
            ->map(function (Column $column) use ($table, $connection) {
                return [
                    'name'     => $column->getName(),
                    'type'     => $column->getType()->getName(),
                    'nullable' => ! $column->getNotnull(),
                    'field'    => null,
                    'label'    => null,
                    'required' => null,
                    'show'     => 'all',
                    'sortable' => null,
                    'relation' => null,
                ];
            });
    }
}