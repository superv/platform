<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Domains\Resource\Field\Watcher;

interface Field
{
    public function getName();

    public function getType();

    public function getColumnName();

    public function setValue($value);

    public function setWatcher(Watcher $watcher);

    public function setValueFromWatcher();

    public function isHidden();

    public function doesNotInteractWithTable();

    public function compose();

    public function hide();
}