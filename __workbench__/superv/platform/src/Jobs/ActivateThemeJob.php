<?php

namespace SuperV\Platform\Jobs;

use Illuminate\View\Factory;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Exceptions\DropletNotFoundException;

class ActivateThemeJob
{
    protected $themeSlug;

    public function __construct($themeSlug)
    {
        $this->themeSlug = $themeSlug;
    }

    public function handle(Factory $view)
    {
        if (! $theme = DropletModel::bySlug($this->themeSlug)) {
            throw new DropletNotFoundException($this->themeSlug);
        }

        $view->addNamespace('theme', base_path($theme->path.'/resources/views'));
    }
}