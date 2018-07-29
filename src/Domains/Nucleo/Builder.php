<?php

namespace SuperV\Platform\Domains\Nucleo;

class Builder
{
    public static function get()
    {
        $builder = \DB::getSchemaBuilder();
        $builder->blueprintResolver(function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}