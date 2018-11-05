<?php

namespace SuperV\Platform\Domains\Resource\Support;

use SuperV\Platform\Domains\Database\Blueprint;

class Blueprints
{
    /**
     * @param \SuperV\Platform\Domains\Database\Blueprint $table
     */
    public static function resources($table)
    {
        $table->increments('id');
        $table->uuid('uuid')->unique();
        $table->string('slug');
        $table->string('droplet_slug');
        $table->text('config')->nullable();

        if ($table instanceof Blueprint) {
            $table->hasMany('sv_resource_fields', 'fields', 'resource_id');
            $table->hasMany('sv_relations', 'relations', 'resource_id');

            $table->resource()->label('Platform Resources');
        }

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Blueprint $table
     */
    public static function fields($table)
    {
        $table->increments('id');
        $table->uuid('uuid');

        if ($table instanceof Blueprint) {
            $table->belongsTo('sv_resources', 'resource');
            $table->resource()->label('Resource Fields');
        } else {
            $table->unsignedInteger('resource_id');
        }

        $table->string('name');
        $table->string('column_type')->nullable();
        $table->string('type');
        $table->boolean('required');
        $table->boolean('unique');
        $table->boolean('searchable');

        $table->text('rules')->nullable();
        $table->text('config')->nullable();
        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Blueprint $table
     */
    public static function relations($table)
    {
        $table->increments('id');
        $table->uuid('uuid');
        if ($table instanceof Blueprint) {
            $table->belongsTo('sv_resources', 'resource');
            $table->resource()->label('Resource Relations');
        } else {
            $table->unsignedInteger('resource_id');
        }

        $table->string('name');
        $table->string('type');
        $table->text('config')->nullable();

        $table->timestamps();
    }
}