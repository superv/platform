<?php

namespace SuperV\Platform\Contracts\Navigation;

interface Navigation
{
    public function addSection(array $section);

    public function addPage(array $page);

    public function make();

    public function getSections();
}