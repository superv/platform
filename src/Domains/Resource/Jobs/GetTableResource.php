<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use Illuminate\Support\Facades\DB;
use SuperV\Platform\Support\Dispatchable;

class GetTableResource
{
    use Dispatchable;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $connection;

    protected static $cache = [];

    public function __construct(string $table, string $connection)
    {
        $this->table = $table;
        $this->connection = $connection;
    }

    public function handle()
    {
        $dsn = sprintf("%s@%s://%s", 'database', $this->connection, $this->table);
        if (! isset(static::$cache[$dsn])) {
            $identifier = DB::table('sv_resources')->where('dsn', $dsn)->value('identifier');

            static::$cache[$dsn] = $identifier ?? false;
        }

        return static::$cache[$dsn];
    }
}
