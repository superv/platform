<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

interface Field
{
    public function getName();

    public function getType();

    public function getColumnName();

    public function isHidden();

    public function doesNotInteractWithTable();

}