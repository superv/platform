<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use Illuminate\Support\Facades\DB;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Support\Dispatchable;

class GetEntryResource
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $entry;

    protected static $cache = [];

    public function __construct(EntryContract $entry)
    {
        $this->entry = $entry;
    }

    public function handle()
    {
        $dsn = $this->getResourceDsn();
        if (! isset(static::$cache[$dsn])) {
            $identifier = DB::table('sv_resources')->where('dsn', $dsn)->value('identifier');

//            if (!$identifier) {
//                PlatformException::fail("Resource for dsn not found: ".$dsn);
//            }

            static::$cache[$dsn] = $identifier ?? false;
        }

        return static::$cache[$dsn];
    }

    protected function getResourceDsn()
    {
        $connection = $this->entry->getConnection()->getName();

        return sprintf("%s@%s://%s", 'database', $connection, $this->entry->getTable());
    }
}
