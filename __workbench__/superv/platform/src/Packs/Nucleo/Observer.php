<?php

namespace SuperV\Platform\Packs\Nucleo;

use Illuminate\Database\Eloquent\Model;

class Observer
{
    protected $cache = [];

    public function retrieved(Model $model)
    {
        foreach ($model->prototype()->fields as $field) {
            if ($field->slug === $model->getKeyName()) {
                continue;
            }

            if ($field->scatter) {
                $model->offsetSet($field->slug, $model->struct()->member($field->slug)->getValue());
            }
        }
    }

    public function saving(Model $model)
    {
        $rules = [];
        $attributes = [];
        $data = [];

        foreach ($model->prototype()->fields as $field) {
            if ($field->slug === $model->getKeyName()) {
                continue;
            }

            if ($field->hasRules()) {
                $rules[$field->slug] = $field->rules;
                $attributes[$field->slug] = sprintf('%s.%s', $model->getTable(), $field->slug);
                $data[$field->slug] = $model->getAttribute($field->slug);
            }

            if ($field->scatter) {
                $model->__cache[$field->slug] = $model->offsetGet($field->slug);
                $model->offsetUnset($field->slug);
            }
        }

        $validator = validator($data, $rules, [], $attributes);
        $validator->validate();
    }

    public function created(Model $model)
    {
        $struct = Struct::create(
            [
                'related_id'   => $model->id,
                'prototype_id' => $model->prototype()->id,
            ]
        );

        $model->fields()->map(function (Field $field) use ($struct, $model) {
            $struct->members()->create(['field_id' => $field->id]);
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

    public function saved(Model $model)
    {
        foreach ($model->prototype()->fields as $field) {
            if ($field->slug === $model->getKeyName()) {
                continue;
            }

            if (! $model->isDirty([$field->slug]) && ! $field->scatter) {
                continue;
            }

            $member = $model->struct()->member($field->slug);

            $value = $field->scatter ? array_get($model->__cache, $field->slug) : $model->getAttribute($field->slug);
            $member->setValue($value);
        }
    }
}