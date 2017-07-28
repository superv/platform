<?php namespace SuperV\Platform\Domains\UI\Button\Features;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\UI\Button\Button;
use SuperV\Platform\Domains\UI\Button\Jobs\EvaluateButtonJob;
use SuperV\Platform\Domains\UI\Button\Jobs\NormalizeButtonJob;

class MakeButton
{
    use DispatchesJobs;

    /**
     * @var array
     */
    private $arguments;

    private $button;

    public function __construct($button, array $arguments)
    {
        $this->arguments = $arguments;
        $this->button = $button;
    }

    public function handle()
    {
        $params = $this->dispatch(new EvaluateButtonJob($this->button, $this->arguments));

        $params = $this->dispatch(new NormalizeButtonJob($params));

        // hydrate button
        $button = superv(Button::class);
        $button->key = array_get($params, 'button');
        $button->attributes = array_get($params, 'attributes', []);
        $button->text = array_get($params, 'text', 'Button');
        $button->type  = array_get($params, 'type', 'default');

        return $button;
    }
}