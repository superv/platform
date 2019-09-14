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

    public function __construct(EntryContract $entry)
    {
        $this->entry = $entry;
    }

    public function handle()
    {
//        $identifier = ResourceModel::query()->where('dsn', $this->getResourceDsn())->value('identifier');

        $identifier = DB::table('sv_resources')->where('dsn', $this->getResourceDsn())->value('identifier');

        return $identifier;
    }

    protected function getResourceDsn()
    {
        $connection = $this->entry->getConnection()->getName();

        return sprintf("%s@%s://%s", 'database', $connection, $this->entry->getTable());
    }
}
