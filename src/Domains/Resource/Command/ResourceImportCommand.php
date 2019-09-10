<?php

namespace SuperV\Platform\Domains\Resource\Command;

use DB;
use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Resource\Generator\ResourceGenerator;
use SuperV\Platform\Domains\Resource\Resource;
use Xethron\MigrationsGenerator\Generators\FieldGenerator;

class ResourceImportCommand extends Command
{
    protected $signature = 'sv:resource:import';

    public function handle()
    {
        $generator = ResourceGenerator::make();

        $generator->setTarget(base_path('database/migrations'));

        $this->getTables()
             ->filter(function ($table) {
                 return true || starts_with($table, 'bill');
             })->map(function ($table) use ($generator) {
                $this->info("Generating resource for table [{$table}]");
                $generator->withTableData($table, ['fields' => $this->getFields($table)]);
            });

//        foreach ($this->getTables() as $table) {
//            $this->info("Generating resource for table [{$table}]");
//            $generator->withTableData($table, ['fields' => $this->getFields($table)]);
//        }
    }

    private function getSchema()
    {
        return DB::connection()->getDoctrineConnection()->getSchemaManager();
    }

    private function getDatabase()
    {
        return DB::connection()->getDoctrineConnection()->getDatabase();
    }

    private function getFields($table)
    {
        $generator = new FieldGenerator();

        return $generator->generate($table, $this->getSchema(), $this->getDatabase(), true);
    }

    protected function getTables()
    {
        return collect($this->getSchema()->listTableNames())
            ->filter(function ($table) {
                return ! starts_with($table, 'sv_');
            })->filter(function ($table) {
                return ! Resource::exists($table);
            });
    }
}
