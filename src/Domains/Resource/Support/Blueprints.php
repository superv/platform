<?php

namespace SuperV\Platform\Domains\Resource\Support;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceModel;

class Blueprints
{
    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $resource
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
            $resource->model(ResourceModel::class);
            $resource->label('Resources');
            $resource->resourceKey('resource');
            $resource->nav('acp.platform.system');

            $table->hasMany('sv_fields', 'fields');
            $table->hasMany('sv_relations', 'relations');
            $table->hasMany('sv_activities', 'activities');
            $table->dictionary('config')->nullable();

            $table->createdBy()->updatedBy();
        } else {
            $table->text('config')->nullable();

            $table->nullableTimestamps();
            $table->unsignedInteger('created_by_id')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
        }
        $table->boolean('restorable')->default(false);
        $table->boolean('sortable')->default(false);
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $resource
     */
    public static function fields($table, ResourceConfig $resource = null)
    {
        $table->increments('id');
        $table->uuid('uuid');

        if ($table instanceof Blueprint) {
            $resource->model(FieldModel::class);
            $resource->label('Fields');
            $resource->resourceKey('field');
            $resource->nav('acp.platform.system');

            $table->nullableBelongsTo('sv_resources', 'resource')->showOnIndex();

            $table->text('flags')->fieldType('array')->nullable();
            $table->dictionary('rules')->nullable();
            $table->dictionary('config')->nullable();
        } else {
            $table->unsignedInteger('resource_id')->nullable();

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
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $resource
     */
    public static function forms($table, ResourceConfig $resource = null)
    {
        $table->increments('id');

        if ($table instanceof Blueprint) {
            $resource->label('Forms');
            $resource->resourceKey('form');
            $resource->nav('acp.platform.forms');
            $resource->model(FormModel::class);

            $table->nullableBelongsTo('sv_resources', 'resource');
            $table->hasUuid()->showOnIndex();
            $table->createdBy()->updatedBy();

            $table->belongsToMany('sv_fields', 'fields')
                  ->pivotTable('sv_form_fields')
                  ->pivotForeignKey('form_id')
                  ->pivotRelatedKey('field_id')
            ->pivotColumns(function(Blueprint $pivotTable) {
                $pivotTable->unsignedInteger('sort_order')->nullable();
            });
        } else {
            $table->unsignedInteger('resource_id')->nullable();
            $table->uuid('uuid');
            $table->boolean('public')->default(false);

            $table->timestamps();
            $table->unsignedInteger('created_by_id')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
        }

        $table->string('title')->showOnIndex();;
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $resource
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
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $resource
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

//        $table->unique(['handle', 'parent_id']);

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $resource
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
}