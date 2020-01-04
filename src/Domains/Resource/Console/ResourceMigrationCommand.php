<?php

namespace SuperV\Platform\Domains\Resource\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Tools\Generator\Domains\Generator;

class ResourceMigrationCommand extends Command
{
    protected $signature = 'resource:migration';

    public function handle(AddonCollection $addons)
    {
        $mode = $this->choice('Will we create a table or update one?', ['0' => 'Create', '1' => 'Update'], 0);
        $addon = $this->choice('Select Addon', sv_addons()->enabled()->slugs()->all());
        if ($mode === 'Update') {
            $allTables = [];
            foreach (\DB::select('SHOW tables') as $key => $table) {
                $allTables[] = head($table);
            }
            $table = $this->askWithCompletion('Select table you want to update', $allTables);
        } else {
            $table = $this->ask('Enter table name');
        }

        $addon = $addons->get($addon);

        $blueprintData = [
            'table'      => $table,
            'identifier' => $addon->getName().'.'.$table,
            'label'      => str_unslug($table),
        ];

        $creator = Generator::resolve();
        $creator->setTargetPath($addon->path('database/migrations'));
        $creator->create($blueprintData);
    }
}