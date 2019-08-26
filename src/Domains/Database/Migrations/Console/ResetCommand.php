<?php

namespace SuperV\Platform\Domains\Database\Migrations\Console;

use Symfony\Component\Console\Input\InputOption;

class ResetCommand extends \Illuminate\Database\Console\Migrations\ResetCommand
{
    public function handle()
    {
        if ($this->option('namespace')) {
            $this->migrator->setNamespace($this->option('namespace'));
        }
        parent::handle();
    }

    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['namespace', null, InputOption::VALUE_OPTIONAL, 'The scope to reset for.'],
            ]
        );
    }
}
