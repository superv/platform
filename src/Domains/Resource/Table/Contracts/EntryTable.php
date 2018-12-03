<?php

namespace SuperV\Platform\Domains\Resource\Table\Contracts;

use Illuminate\Support\Collection;

interface EntryTable extends Table
{
    public function getFilters(): Collection;

    public function setFilters($filters): EntryTable;

    public function setRequest($request): EntryTable;

    public function getQuery();

    public function setQuery($query): EntryTable;

    public function getPagination(): array;
}