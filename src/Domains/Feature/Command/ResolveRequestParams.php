<?php namespace SuperV\Platform\Domains\Feature\Command;

use SuperV\Platform\Domains\Feature\Feature;

class ResolveRequestParams
{
    /**
     * @var \SuperV\Platform\Domains\Feature\Feature
     */
    private $feature;

    /**
     * @var array
     */
    private $input;

    private $resolvable;

    public function __construct($resolvable, array $input)
    {
        $this->input = $input;
        $this->resolvable = $resolvable;
    }

    public function handle()
    {
        $params = [];
        if ($resolves = $this->resolvable) {
            foreach ($resolves as $key => $resolver) {
                if (!$value = array_get($this->input, $key)) {
                    continue; // TODO.ali: validation
                }
                if (false !== strpos($resolver, '->')) {
                    list($resolver, $property) = explode('->', $resolver);
                }

                if (!$resolved = superv($resolver)->find($value)) {
                    continue; // TODO.ali: required validation
                }
                array_set($params, $key, isset($property) ? $resolved->{$property} : $resolved);
            }
        }

        return $params;
    }
}