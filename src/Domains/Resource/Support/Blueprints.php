<?php

namespace SuperV\Platform\Domains\Resource\Support;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Support\Meta\MetaModel;

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
        $table->string('addon');
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
            $resource->label('Resource Fields');
            $table->belongsTo('sv_resources', 'resource');
            $table->morphOne('sv_meta', 'configMeta', 'owner');
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
            $resource->label('Resource Navigation');

            $table->nullableBelongsTo('sv_navigation', 'parent');
            $table->nullableBelongsTo('sv_resources', 'resource');
        } else {
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('resource_id')->nullable();
        }

        $table->string('title')->entryLabel();
        $table->string('handle');
        $table->string('icon')->nullable();
        $table->string('url')->nullable();

//        $table->unique(['handle', 'parent_id']);  @TODO: test

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function meta($table, ResourceBlueprint $resource = null)
    {
        $table->increments('id');

        if ($table instanceof Blueprint) {
            $resource->label('Meta');
//            $resource->model(MetaModel::class);
            $table->hasMany('sv_meta_items', 'items', 'meta_id', 'id');
        }

        $table->nullableMorphs('owner');
        $table->string('label')->nullable();
        $table->uuid('uuid')->nullable();

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function metaItems($table, ResourceBlueprint $resource = null)
    {
        $table->increments('id');

        if ($table instanceof Blueprint) {
            $resource->label('Meta Items');

            $table->nullableBelongsTo('sv_meta', 'meta');
            $table->nullableBelongsTo('sv_meta_items', 'parent_item');
            $table->hasMany('sv_meta_items', 'items', 'parent_item_id', 'id');
        } else {
            $table->unsignedInteger('meta_id')->nullable();
            $table->unsignedInteger('parent_item_id')->nullable();
        }

        $table->string('key');
        $table->text('value')->nullable();
    }
}