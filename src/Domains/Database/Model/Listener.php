<?php

namespace SuperV\Platform\Domains\Database\Model;

use Illuminate\Database\Events\QueryExecuted;
use SuperV\Platform\Domains\Database\Events\TableInsertEvent;
use SuperV\Platform\Domains\Database\Events\TableUpdateEvent;

class Listener
{
    public static function listen()
    {
        \DB::listen(function (QueryExecuted $query) {
            if (! starts_with($query->sql, ['insert', 'update'])) {
                return;
            }

            $parts = explode(' ', $query->sql);

            $operation = $parts[0];
            if ($operation === 'insert') {
                $table = trim($parts[2], '"');

                TableInsertEvent::dispatch($table);
            } elseif (true || $operation === 'update') {
                $table = trim($parts[1], '"');

                TableUpdateEvent::dispatch($table, last($query->bindings));
            }
        });
    }
}