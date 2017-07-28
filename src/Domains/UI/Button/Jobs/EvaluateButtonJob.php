<?php namespace SuperV\Platform\Domains\UI\Button\Jobs;

use SuperV\Platform\Support\Parser;

class EvaluateButtonJob
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