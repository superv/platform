<?php

namespace SuperV\Platform\Domains\Database\Migrations\Console;

use Symfony\Component\Console\Input\InputOption;

class RefreshCommand extends \Illuminate\Database\Console\Migrations\RefreshCommand
{
    public function call($command, array $arguments = [])
    {
        if ($this->option('scope')) {
            if (in_array($command, ['migrate', 'migrate:rollback', 'migrate:reset'])) {
                array_set($arguments, '--scope', $this->option('scope'));
            }
        }

        return parent::call($command, $arguments);
    }

    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['scope', null, InputOption::VALUE_OPTIONAL, 'The scope to rollback for.'],
            ]
        );
    }
}