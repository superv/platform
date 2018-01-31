<?php

namespace SuperV\Platform\Packs\Nucleo;

use Illuminate\Database\Eloquent\Model;

class Observer
{
    public function created(Model $model)
    {
        $struct = Struct::create(
            [
                'related_id'   => $model->id,
                'prototype_id' => $model->prototype()->id,
            ]
        );

        $model->fields()->map(function (Field $field) use ($struct, $model) {
            $struct->members()->create(
                [
                    'field_id' => $field->id,
                ]
            );
        });
    }

    public function deleted(Model $model)
    {
        $prototype = Prototype::where('table', $model->getTable())->first();

        $struct = $prototype->structs()->where('related_id', $model->id)->first();

        if ($struct) {
            $struct->delete();
        }
    }

    public function saving(Model $model)
    {
        $rules = [];
        $attributes = [];

        foreach ($model->getDirty() as $key => $value) {
            if ($key === $model->getKeyName()) {
                continue;
            }
            $field = $model->prototype()->field($key);

            if ($field->hasRules()) {
                $rules[$field->slug] = $field->rules;
                $attributes[$field->slug] = sprintf('%s.%s', $model->getTable(), $field->slug);
            }
        }

        $validator = validator($model->toArray(), $rules, [], $attributes);
        $validator->validate();
    }

    public function saved(Model $model)
    {
        foreach ($model->getDirty() as $key => $value) {
            if ($key === $model->getKeyName()) {
                continue;
            }
            $member = $model->struct()->member($key);
            $member->setValue($value);
        }
    }
}