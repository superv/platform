<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;

class AddonMakeMigrationCommand extends Command
{
    protected $signature = 'addon:migration';

    public function handle()
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
        $name = $this->ask('Would you like to add something to migration name?', '');

        $name = $name ? '_'.str_slug($name, '_') : '';

        if ($mode === 'Create') {
            $arguments = [
                'name'        => "create_{$table}_table".$name,
                '--create'    => $table,
                '--namespace' => $addon,
            ];
        } else {
            $arguments = [
                'name'        => "alter_{$table}_table".$name,
                '--table'     => $table,
                '--namespace' => $addon,
            ];
        }

        $this->call('make:migration', $arguments);
    }
}
