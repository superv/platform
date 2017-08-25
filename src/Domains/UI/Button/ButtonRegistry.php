<?php namespace SuperV\Platform\Domains\UI\Button;

class ButtonRegistry
{
    protected $buttons = [
        'view'   => [
            'text' => 'View',
            'icon' => 'fa fa-eye',
            'type' => 'info',
        ],
        'index'  => [
            'text'  => 'Index',
            'icon'  => 'fa fa-list',
            'type'  => '',
            'class' => 'bg-orange',

        ],
        'edit'   => [
            'text'        => 'Edit',
            'icon'        => 'fa fa-save',
            'type'        => 'warning',
        ],
        'create' => [
            'text' => 'Create',
            'icon' => 'fa fa-plus',
            'type' => 'success',
        ],
        'delete' => [
            'text' => 'Delete',
            'icon' => 'fa fa-remove',
            'type' => 'danger',
            'attributes' => [
                           'data-toggle'  => 'confirm',
                'data-target' => 'modal-danger',
                           'data-message' => 'Are you sure you want to delete this?',
                       ],
        ],
    ];

    public function get($button)
    {
        if (!$button) {
            return null;
        }

        return array_get($this->buttons, $button);
    }
}