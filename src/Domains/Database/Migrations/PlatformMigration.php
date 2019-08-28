<?php

namespace SuperV\Platform\Domains\Database\Migrations;

interface PlatformMigration
{
    public function getNamespace();

    public function setNamespace($namespace);
}
