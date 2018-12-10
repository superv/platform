<?php

namespace SuperV\Platform\Domains\Resource\Support;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class Blueprints
{
    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function resources($table, ResourceConfig $resource = null)
    {
        $table->increments('id');
        $table->uuid('uuid')->unique();
        $table->string('handle');
        $table->string('slug');
        $table->string('addon')->showOnIndex();
        $table->string('model')->nullable();

        if ($table instanceof Blueprint) {
            $resource->label('Resources');
            $resource->resourceKey('resource');
            $resource->nav('acp.platform.system');

            $table->hasMany('sv_fields', 'fields');
            $table->hasMany('sv_relations', 'relations');
            $table->hasMany('sv_activities', 'activities');
            $table->dictionary('config')->nullable();
        } else {
            $table->text('config')->nullable();
        }
        $table->boolean('restorable')->default(false);
        $table->boolean('sortable')->default(false);

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function fields($table, ResourceConfig $resource = null)
    {
        $table->increments('id');
        $table->uuid('uuid');

        if ($table instanceof Blueprint) {
            $resource->label('Fields');
            $resource->resourceKey('field');
            $resource->nav('acp.platform.system');

            $table->belongsTo('sv_resources', 'resource')->showOnIndex();
            $table->morphOne('sv_meta', 'configMeta', 'owner');

            $table->text('flags')->fieldType('array')->nullable();
            $table->dictionary('rules')->nullable();
            $table->dictionary('config')->nullable();
        } else {
            $table->unsignedInteger('resource_id');

            $table->text('flags')->nullable();
            $table->text('rules')->nullable();
            $table->text('config')->nullable();
        }

        $table->string('name')->showOnIndex();;
        $table->string('column_type')->nullable();
        $table->string('type')->showOnIndex();;

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function relations($table, ResourceConfig $resource = null)
    {
        $table->increments('id');
        $table->uuid('uuid');
        if ($table instanceof Blueprint) {
            $resource->label('Relations');
            $resource->resourceKey('relation');
            $resource->nav('acp.platform.system');

            $table->belongsTo('sv_resources', 'resource')->showOnIndex();;
            $table->dictionary('config')->nullable();
        } else {
            $table->unsignedInteger('resource_id');
            $table->text('config')->nullable();
        }

        $table->string('name')->showOnIndex();;
        $table->string('type')->showOnIndex();;

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function navigation($table, ResourceConfig $resource = null)
    {
        $table->increments('id');
        if ($table instanceof Blueprint) {
            $resource->label('Navigation');
            $resource->resourceKey('nav');
            $resource->nav('acp.platform.system');

            $table->nullableBelongsTo('sv_navigation', 'parent')->showOnIndex();;
            $table->nullableBelongsTo('sv_resources', 'resource')->showOnIndex();;
        } else {
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('resource_id')->nullable();
        }

        $table->string('title')->entryLabel();
        $table->string('handle');
        $table->string('addon')->nullable();
        $table->string('icon')->nullable();
        $table->string('url')->nullable();

//        $table->unique(['handle', 'parent_id']);  @TODO: test

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function activity($table, ResourceConfig $resource = null)
    {
        $table->increments('id');
        if ($table instanceof Blueprint) {
            $resource->nav('acp.platform.system');
            $resource->label('Resource Activity');

            $table->belongsTo('sv_resources', 'resource')->showOnIndex();
            $table->belongsTo('users', 'user')->showOnIndex();
            $table->nullableMorphTo('entry')->showOnIndex();
            $table->dictionary('payload');
        } else {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('resource_id');
            $table->nullableMorphs('entry');
            $table->text('payload')->nullable();
        }

        $table->string('activity')->entryLabel();
        $table->timestamp('created_at')->showOnIndex();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function meta($table, ResourceConfig $resource = null)
    {
        $table->increments('id');

        if ($table instanceof Blueprint) {
            $resource->label('Meta');
//            $resource->model(MetaModel::class);
            $table->hasMany('sv_meta_items', 'items', 'meta_id');
        }

        $table->nullableMorphs('owner');
        $table->string('label')->nullable();
        $table->uuid('uuid')->nullable();

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint $table
     */
    public static function metaItems($table, ResourceConfig $resource = null)
    {
        $table->increments('id');

        if ($table instanceof Blueprint) {
            $resource->label('Meta Items');

            $table->nullableBelongsTo('sv_meta', 'meta');
            $table->nullableBelongsTo('sv_meta_items', 'parent_item');
            $table->hasMany('sv_meta_items', 'items', 'parent_item_id');
        } else {
            $table->unsignedInteger('meta_id')->nullable();
            $table->unsignedInteger('parent_item_id')->nullable();
        }

        $table->string('key');
        $table->text('value')->nullable();
    }
}