<?php

namespace SuperV\Platform\Domains\Database\Migrations\Console;

use Symfony\Component\Console\Input\InputOption;

class RefreshCommand extends \Illuminate\Database\Console\Migrations\RefreshCommand
{
    public function call($command, array $arguments = [])
    {
        if ($this->option('addon')) {
            if (in_array($command, ['migrate', 'migrate:rollback', 'migrate:reset'])) {
                array_set($arguments, '--addon', $this->option('addon'));
            }
        }

        return parent::call($command, $arguments);
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