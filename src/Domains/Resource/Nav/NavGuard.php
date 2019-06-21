<?php

namespace SuperV\Platform\Domains\Resource\Nav;

use SuperV\Platform\Domains\Auth\Contracts\User;

class NavGuard
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Nav\Nav
     */
    protected $nav;

    /**
     * @var \SuperV\Platform\Domains\Auth\User
     */
    protected $user;

    public function __construct(User $user, Nav $nav)
    {
        $this->nav = $nav;
        $this->user = $user;
    }

    public function compose()
    {
        $composed = $this->nav->compose($withColophon = true);

        $composed['sections'] = $this->filterSections($composed['sections']);

        return $composed;
    }

    protected function filterSections($sections)
    {
        foreach ($sections as $key => $section) {
            if ($colophon = array_pull($section, 'colophon')) {
                // remove navigation name from colophon
                // because it's not set in actions for now
                //
                $colophon = str_replace_first($this->nav->entry()->getHandle().'.', '', $colophon);
                if (! $this->user->can($colophon)) {
                    unset($sections[$key]);
                    continue;
                }
                if ($subSections = $section['sections'] ?? null) {
                    $section['sections'] = $this->filterSections($subSections);
                }
            }

            $sections[$key] = array_filter($section);
        }

        return $sections;
//        return array_values($sections);
    }
}
