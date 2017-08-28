<?php namespace SuperV\Platform\Domains\UI\Button\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Button\Jobs\NormalizeButtonUrl;

/**
 * Class NormalizeButton
 *
 * Refactorables: * $params to ButtonsParams object
 *
 * @package SuperV\Platform\Domains\UI\Button\Features
 */
class NormalizeButton extends Feature
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
        $params = $this->button;

        /*
         * Make sure some default parameters exist.
         */
        $params['attributes'] = array_get($params, 'attributes', []);

        $params = $this->dispatch(new NormalizeButtonUrl($params, $this->arguments));

        /*
         * Move the target if any to the attributes.
         */
        if (isset($params['target'])) {
            array_set($params['attributes'], 'target', array_pull($params, 'target'));
        }

        if ($after = array_pull($params, 'after')) {
            if (str_is('*@*::*', $after)) {
                array_set($params, 'attributes.data-after', $after);
            }
        }

        if (is_string($href = array_get($params, 'attributes.href')) && !starts_with($href, 'http')) {
            $params['attributes']['href'] = url($params['attributes']['href']);
        }

        foreach ($params as $attribute => $value) {
            if (str_is('data-*', $attribute)) {
                array_set($params, 'attributes.' . $attribute, array_pull($params, $attribute));
            }
        }

        if (!array_get($params, 'text')) {
            array_set($params, 'text', ucwords(str_replace('_', ' ', array_get($params, 'button'))));
        }

        return $params;
    }
}