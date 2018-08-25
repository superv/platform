<?php

namespace SuperV\Platform\Domains\UI\Table;

use SuperV\Platform\Domains\Setting\JSON;
use SuperV\Platform\Support\Concerns\Hydratable;

class TableFactory
{

    /**
     * @var Table
     */
    private $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /** @return TableBuilder */
    public function fromJson($id)
    {
        $data = (new JSON(storage_path("superv/compiled/table/{$id}")))->data;

        $builder = app(TableBuilder::class);

        $builder->hydrate($data);

        return $builder;
    }
}