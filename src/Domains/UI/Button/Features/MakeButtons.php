<?php

namespace SuperV\Platform\Domains\UI\Button\Features;

use SuperV\Platform\Support\Hydrator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\UI\Button\Button;
use SuperV\Platform\Domains\UI\Button\ButtonRegistry;

class MakeButtons
{
    use DispatchesJobs;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var array
     */
    private $buttons;

    public function __construct(array $buttons, array $arguments = [])
    {
        $this->arguments = $arguments;
        $this->buttons = $buttons;
    }

    public function handle()
    {
        $buttons = [];

        foreach ($this->buttons as $key => $button) {
            if (is_numeric($key) && is_string($button)) {
                $button = [
                    'button' => $button,
                ];
            }

            if (! is_numeric($key) && is_string($button)) {
                $button = [
                    'text'   => $button,
                    'button' => $key,
                ];
            }

            if (! is_numeric($key) && is_array($button) && ! isset($button['button'])) {
                $button['button'] = $key;
            }

            $buttons[] = $this->makeButton($button);
        }

        return $buttons;
    }

    public function makeButton($params)
    {
        if ($registered = superv(ButtonRegistry::class)->get($params['button'])) {
            $params = array_replace_recursive($registered, $params);
        }

        $params = $this->dispatch(new EvaluateButton($params, $this->arguments));
        $params = $this->dispatch(new NormalizeButton($params, $this->arguments));

        // hydrate button
        return superv(Hydrator::class)->hydrate(superv(Button::class), $params);
//        $button = superv(Button::class);
//        $button->button = array_get($params, 'button');
//        $button->attributes = array_get($params, 'attributes', []);
//        $button->text = array_get($params, 'text', 'Button');
//        $button->type = array_get($params, 'type', 'default');
//
//        return $button;
    }
}
