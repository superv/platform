<?php

namespace SuperV\Platform\Domains\Nucleo;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Nucleo\Contracts\Structable;

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
                $rules[$field->slug] = $field->getRules();
                $attributes[$field->slug] = sprintf('%s.%s', $model->getTable(), $field->slug);
                $data[$field->slug] = $model->getAttribute($field->slug);
            }

            if ($field->scatter) {
                $model->__cache[$field->slug] = $model->offsetGet($field->slug);
                /** hate */
                $model->offsetUnset($field->slug);
            }
        }

        $validator = validator($data, $rules, [], $attributes);
        $validator->validate();
    }

    public function created(Model $model)
    {
        if ($model instanceof Structable) {
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
    }

    public function deleted(Model $model)
    {
        if ($model instanceof Structable) {
            $prototype = Prototype::where('slug', $model->getTable())->first();

            $struct = $prototype->structs()->where('related_id', $model->id)->first();

            if ($struct) {
                $struct->delete();
            }
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

            if ($model instanceof Structable) {
                $member = $model->struct()->member($field->slug);

                $value = $field->scatter ? array_get($model->__cache/** hate */, $field->slug) : $model->getAttribute($field->slug);
                $member->setValue($value);
            }
        }
    }
}