<?php

namespace SuperV\Platform\Domains\Resource\Command;

use Illuminate\Support\Facades\DB;
use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Resource\Generator\FieldGenerator;
use SuperV\Platform\Domains\Resource\Generator\ResourceGenerator;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceImportCommand extends Command
{
    protected $signature = 'sv:resource:import {--connection=}';

    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    protected $tables;

    protected $excluded = ['migrations', 'jobs', 'users', 'password_resets', 'failed_jobs'];

    public function handle()
    {
        $this->prepareConnection();
        $generator = ResourceGenerator::make();

        $generator->setTarget(base_path('database/migrations'));

        $this->getTables()
             ->filter(function ($table) {
                 return true || starts_with($table, 'bill');
             })->map(function ($table) use ($generator) {
                $this->info("Generating resource for table [{$table}]");
                $generator->withTableData($table, ['fields' => $this->getFields($table)]);
            });
    }

    protected function prepareConnection()
    {
        $connectionName = $this->option('connection');
        $connection = DB::connection($connectionName)->getDoctrineConnection();;

        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('jsonb', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        // Postgres types
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_text', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_int4', 'integer');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_numeric', 'float');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('cidr', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('inet', 'string');

        $this->connection = $connection;
    }

    private function getSchema()
    {
        return $this->connection->getSchemaManager();
    }

    private function getDatabase()
    {
        return $this->connection->getDatabase();
    }

    private function getFields($table)
    {
        $generator = new FieldGenerator();

        return $generator->generate($table, $this->getSchema(), $this->getDatabase(), true);
    }

    protected function getTables()
    {
        $tables = $this->tables ?? $this->getSchema()->listTableNames();

        return collect($tables)
            ->filter(function ($table) {
                return ! in_array($table, $this->excluded);
            })->filter(function ($table) {
                return ! starts_with($table, 'sv_');
            })->filter(function ($table) {
                return ! Resource::exists($table);
            });
    }
}
