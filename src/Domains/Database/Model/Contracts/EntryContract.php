<?php

namespace SuperV\Platform\Domains\Database\Model\Contracts;

interface EntryContract extends Watcher
{
    public function getId();

    public function getTable();

}