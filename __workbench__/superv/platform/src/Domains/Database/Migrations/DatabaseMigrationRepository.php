<?php

namespace SuperV\Platform\Domains\Database\Migrations;

class DatabaseMigrationRepository extends \Illuminate\Database\Migrations\DatabaseMigrationRepository
{
    public function createRepository()
    {
        parent::createRepository();

        $schema = $this->getConnection()->getSchemaBuilder();
        $schema->table($this->table, function ($table) {
            $table->string('scope')->nullable();
        });
    }
}