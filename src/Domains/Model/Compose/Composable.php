<?php namespace Merpa\SupportModule\Compose;

trait Composable
{
    public function compose($params)
    {
        $composerClass = str_replace_last('Model', 'Composer', get_class($this));
        if (class_exists($composerClass)) {
            return (new $composerClass($this))->compose($params);
        }
    }
}