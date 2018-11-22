<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;

interface Field
{
    public function getName();

    public function getType();

    public function getColumnName();

    public function setValue($value);

    public function setWatcher(Watcher $watcher);

    public function setValueFromWatcher();

    public function isHidden();

    public function isUnique();

    public function doesNotInteractWithTable();

    public function compose();

    public function hide();

    public function fieldType(): FieldType;

    public function onPresenting(Closure $callback);
}