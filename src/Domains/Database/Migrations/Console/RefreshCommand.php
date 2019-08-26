<?php

namespace SuperV\Platform\Domains\Database\Migrations\Console;

use Symfony\Component\Console\Input\InputOption;

class RefreshCommand extends \Illuminate\Database\Console\Migrations\RefreshCommand
{
    public function call($command, array $arguments = [])
    {
        if ($this->option('namespace')) {
            if (in_array($command, ['migrate', 'migrate:rollback', 'migrate:reset'])) {
                array_set($arguments, '--namespace', $this->option('namespace'));
            }
        }

        return parent::call($command, $arguments);
    }

    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['namespace', null, InputOption::VALUE_OPTIONAL, 'The scope to rollback for.'],
            ]
        );
    }
}
