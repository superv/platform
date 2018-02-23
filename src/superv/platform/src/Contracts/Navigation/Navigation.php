<?php

namespace SuperV\Platform\Contracts\Navigation;

use SuperV\Platform\Domains\Droplet\Port\Port;

interface Navigation
{
    public function addSection(array $section);

    public function addPage(array $page);

    public function make();

    public function getSections();

    public function getPort();

    public function setPort(Port $port);
}