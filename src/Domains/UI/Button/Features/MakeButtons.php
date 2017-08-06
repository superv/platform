<?php namespace SuperV\Platform\Domains\UI\Button\Features;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\UI\Button\Button;
use SuperV\Platform\Domains\UI\Button\Jobs\EvaluateButtonJob;
use SuperV\Platform\Domains\UI\Button\Jobs\NormalizeButtonJob;

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

    public function __construct(array $buttons, array $arguments)
    {
        $this->arguments = $arguments;
        $this->buttons = $buttons;
    }

    public function handle()
    {
        $buttons = [];

        foreach ($this->buttons as $button) {
            $buttons[] = $this->makeButton($button);
        }

        return $buttons;
    }

    public function makeButton($data)
    {
        $params = $this->dispatch(new EvaluateButtonJob($data, $this->arguments));

        $params = $this->dispatch(new NormalizeButtonJob($params));

        // hydrate button
        $button = superv(Button::class);
        $button->key = array_get($params, 'button');
        $button->attributes = array_get($params, 'attributes', []);
        $button->text = array_get($params, 'text', 'Button');
        $button->type = array_get($params, 'type', 'default');

        return $button;
    }
}