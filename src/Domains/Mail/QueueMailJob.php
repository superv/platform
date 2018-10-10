<?php

namespace SuperV\Platform\Domains\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use SuperV\Platform\Support\Dispatchable;

class QueueMailJob implements ShouldQueue
{
    use Dispatchable;

    protected $template;

    protected $to;

    protected $params;

    public function __construct($template, $to, $params)
    {
        $this->template = $template;
        $this->to = $to;
        $this->params = $params;
    }

    public function handle()
    {
        TemplateSender::template($this->template)
                      ->params($this->params)
                      ->to($this->to)
                      ->sender()
                      ->send();
    }
}