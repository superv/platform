<?php

namespace SuperV\Platform\Domains\UI\Button\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Support\Parser;

class EvaluateButton extends Feature
{
    private $button;

    /**
     * @var array
     */
    private $arguments;

    public function __construct($button, array $arguments)
    {
        $this->button = $button;
        $this->arguments = $arguments;
    }

    public function handle(Parser $evaluator)
    {
        return $evaluator->parse($this->button, $this->arguments);
    }
}
