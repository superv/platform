<?php

namespace SuperV\Platform\Packs\Database\Migrations\Console;

use Symfony\Component\Console\Input\InputOption;

class ResetCommand extends \Illuminate\Database\Console\Migrations\ResetCommand
{
    public function handle()
    {
        if ($this->option('scope')) {
            $this->migrator->setScope($this->option('scope'));
        }
        parent::handle();
    }

    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['scope', null, InputOption::VALUE_OPTIONAL, 'The scope to reset for.'],
            ]
        );
    }
}