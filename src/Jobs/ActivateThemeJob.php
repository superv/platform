<?php

namespace SuperV\Platform\Jobs;

use Illuminate\View\Factory;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Events\ThemeActivatedEvent;
use SuperV\Platform\Exceptions\AddonNotFoundException;

class ActivateThemeJob
{
    protected $themeSlug;

    public function __construct($themeSlug)
    {
        $this->themeSlug = $themeSlug;
    }

    public function handle(Factory $view)
    {
        if (! $theme = AddonModel::bySlug($this->themeSlug)) {
            throw new AddonNotFoundException($this->themeSlug);
        }

        $view->addNamespace('theme', base_path($theme->path.'/resources/views'));

        ThemeActivatedEvent::dispatch($theme);
    }
}