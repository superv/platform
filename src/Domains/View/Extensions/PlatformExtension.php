<?php namespace SuperV\Platform\Domains\View\Extensions;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;
use Twig_SimpleFunction;

class PlatformExtension extends \Twig_Extension
{
    use DispatchesJobs;

    public function getFunctions()
        {
            return [
                new Twig_SimpleFunction('buttons', function($buttons) {
                    return $this->dispatch(new MakeButtons($buttons));
                },  [
                                    'is_safe' => ['html'],
                                ]),
            ];
        }
}