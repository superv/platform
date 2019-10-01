<?php

namespace SuperV\Platform\Domains\Resource\Table\Contracts;

use Illuminate\Support\Collection;

interface TableDataProviderInterface
{
    public function setQuery($query): void;

    public function setRowsPerPage($count): void;

    public function fetch(): void;

    public function getEntries(): Collection;

    public function getPagination(): array;
}