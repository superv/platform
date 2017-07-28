<?php namespace SuperV\Platform\Domains\UI\Form\Extension\Http;

use Symfony\Component\Form\AbstractExtension;


class HttpExtension extends AbstractExtension
{
    protected function loadTypeExtensions()
    {
        return array(
            new FormTypeHttpExtension(),
        );
    }
}
