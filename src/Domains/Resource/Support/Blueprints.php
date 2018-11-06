<?php

namespace SuperV\Platform\Domains\Resource\Support;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;

class Blueprints
{
    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function resources($table, ResourceBlueprint $resource = null)
    {
        $table->increments('id');
        $table->uuid('uuid')->unique();
        $table->string('slug');
        $table->string('droplet');
        $table->string('model')->nullable();
        $table->text('config')->nullable();

        if ($table instanceof Blueprint) {
            $table->hasMany('sv_fields', 'fields', 'resource_id');
            $table->hasMany('sv_relations', 'relations', 'resource_id');

            $resource->label('Platform Resources');
        }

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function fields($table, ResourceBlueprint $resource = null)
    {
        $table->increments('id');
        $table->uuid('uuid');

        if ($table instanceof Blueprint) {
            $table->belongsTo('sv_resources', 'resource');
            $resource->label('Resource Fields');
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
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function relations($table, ResourceBlueprint $resource = null)
    {
        $table->increments('id');
        $table->uuid('uuid');
        if ($table instanceof Blueprint) {
            $table->belongsTo('sv_resources', 'resource');
            $resource->label('Resource Relations');
        } else {
            $table->unsignedInteger('resource_id');
        }

        $table->string('name');
        $table->string('type');
        $table->text('config')->nullable();

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function navigation($table, ResourceBlueprint $resource = null)
    {
        $table->increments('id');
        if ($table instanceof Blueprint) {
            $table->belongsTo('sv_resources', 'resource');
            $resource->label('Resource Navigation');
        } else {
            $table->unsignedInteger('resource_id');
        }

        $table->string('nav');
        $table->string('section')->nullable();
        $table->string('subsection')->nullable();
        $table->string('slug');
        $table->string('title')->entryLabel();

        $table->timestamps();
    }
}