<?php

namespace SuperV\Platform\Domains\UI\Form\Extension;

use Symfony\Component\Form\AbstractExtension;

/**
 * Give access to the session to the Form.
 */
class SessionExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadTypeExtensions()
    {
        return [
            new Session\CsrfTypeExtension,
            new Session\SessionTypeExtension,
        ];
    }
}
