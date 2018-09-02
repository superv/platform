<?php

namespace SuperV\Platform\Domains\Navigation;

interface HasSection
{
    public static function getSection(): Section;
}