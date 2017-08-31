<?php

namespace SuperV\Platform\Domains\Feature\Command;

class ResolveRequestParamsJob
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

        foreach ($this->input as $inputKey => $inputValue) {
            if ($resolver = array_get($this->resolvable, $inputKey)) {
                if (false !== strpos($resolver, '->')) {
                    list($resolver, $property) = explode('->', $resolver);
                }

                if ($resolved = app($resolver)->find($inputValue)) {
                    $inputValue = isset($property) ? $resolved->{$property} : $resolved;
                }
            }
            array_set($params, $inputKey, $inputValue);
        }

//        if ($resolves = $this->resolvable) {
//            foreach ($resolves as $key => $resolver) {
//                if (!$value = array_get($this->input, $key)) {
//                    continue; // TODO.ali: validation
//                }
//                if (false !== strpos($resolver, '->')) {
//                    list($resolver, $property) = explode('->', $resolver);
//                }
//
//                if (!$resolved = app($resolver)->find($value)) {
//                    continue; // TODO.ali: required validation
//                }
//                array_set($params, $key, isset($property) ? $resolved->{$property} : $resolved);
//            }
//        }

        return $params;
    }
}
