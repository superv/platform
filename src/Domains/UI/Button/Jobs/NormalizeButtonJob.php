<?php namespace SuperV\Platform\Domains\UI\Button\Jobs;

use SuperV\Platform\Domains\Entry\EntryModel;

class NormalizeButtonJob
{
    private $button;

    private $arguments;

    public function __construct($button, $arguments)
    {
        $this->button = $button;
        $this->arguments = $arguments;
    }

    public function handle()
    {
        $buttonData = $this->button;

        /*
         * Make sure some default parameters exist.
         */
        $buttonData['attributes'] = array_get($buttonData, 'attributes', []);

        /*
         * Move the HREF if any to the attributes.
         */
        if (isset($buttonData['href'])) {
            array_set($buttonData['attributes'], 'href', array_pull($buttonData, 'href'));
        } elseif ($entry = array_get($this->arguments, 'entry')) {
            if ($entry instanceof EntryModel && $button = array_get($buttonData, 'button')) {
                array_set($buttonData, 'attributes.href', $entry->route($button));
            }
        } elseif ($route = array_get($buttonData, 'route')) {
            array_set($buttonData, 'attributes.href', route($route));
        }

        /*
         * Move the target if any to the attributes.
         */
        if (isset($buttonData['target'])) {
            array_set($buttonData['attributes'], 'target', array_pull($buttonData, 'target'));
        }

        if (
            isset($buttonData['attributes']['href']) &&
            is_string($buttonData['attributes']['href']) &&
            !starts_with($buttonData['attributes']['href'], 'http')
        ) {
            $buttonData['attributes']['href'] = url($buttonData['attributes']['href']);
        }

        foreach ($buttonData as $attribute => $value) {
            if (str_is('data-*', $attribute)) {
                array_set($buttonData, 'attributes.' . $attribute, array_pull($buttonData, $attribute));
            }
        }

        return $buttonData;
    }
}