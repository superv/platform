<?php

namespace SuperV\Platform\Domains\Resource\Table\Contracts;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface Column
{
    public function getName();

    public function getLabel();

    public function setLabel(string $label): self;

    public function setTemplate(string $template): self;

    public function getPresenter();

    public function setPresenter(Closure $callback): self;

    public function getAlterQueryCallback();

    public function present(EntryContract $entry);

    public function getConfigValue($key);
}