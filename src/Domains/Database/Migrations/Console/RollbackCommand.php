<?php

namespace SuperV\Platform\Domains\Database\Migrations\Console;

use Symfony\Component\Console\Input\InputOption;

class RollbackCommand extends \Illuminate\Database\Console\Migrations\RollbackCommand
{
    /** @var \SuperV\Platform\Domains\Database\Migrations\Migrator */
    protected $migrator;

    public function handle()
    {
        if ($this->option('addon')) {
            $this->migrator->setAddon($this->option('addon'));
        }
        parent::handle();
    }

    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['addon', null, InputOption::VALUE_OPTIONAL, 'The scope to rollback for.'],
            ]
        );
    }
}