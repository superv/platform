<?php namespace SuperV\Platform\Domains\UI\Button\Jobs;

class NormalizeButtonJob
{
    private $button;

    public function __construct($button)
    {
        $this->button = $button;
    }

    public function handle()
    {
        $button = $this->button;

        /*
         * Make sure some default parameters exist.
         */
        $button['attributes'] = array_get($button, 'attributes', []);

        /*
         * Move the HREF if any to the attributes.
         */
        if (isset($button['href'])) {
            array_set($button['attributes'], 'href', array_pull($button, 'href'));
        }

        /*
         * Move the target if any to the attributes.
         */
        if (isset($button['target'])) {
            array_set($button['attributes'], 'target', array_pull($button, 'target'));
        }

        if (
            isset($button['attributes']['href']) &&
            is_string($button['attributes']['href']) &&
            !starts_with($button['attributes']['href'], 'http')
        ) {
            $button['attributes']['href'] = url($button['attributes']['href']);
        }

        return $button;
    }
}