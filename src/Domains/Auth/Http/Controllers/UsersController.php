<?php namespace SuperV\Platform\Domains\Auth\Http\Controllers;

use SuperV\Modules\Ui\Domains\Form\FormFactory;
use SuperV\Modules\Ui\Domains\Form\Jobs\MakeFormInstance;
use SuperV\Modules\Ui\Domains\Form\Jobs\MapForm;
use SuperV\Modules\Ui\Domains\Table\TableFactory;
use SuperV\Platform\Domains\Auth\Domains\User\Users;
use SuperV\Platform\Http\Controllers\BasePlatformController;

class UsersController extends BasePlatformController
{
    public function index(TableFactory $factory)
    {
        $builder = $factory->fromJson('users.json');
        $table = $builder->build()->getTable();

        $data = [
            'block' => [
                'component' => 'sv-table',
                'props'     => [
                    'columns' => $table->getColumns(),
                    'rows'    => $table->getRows(),
                ],
            ],
            'page'  => [
                'title'   => 'Users Index',
                'buttons' => [
                    [
                        'title'      => 'New User',
                        'button'     => 'create',
                        'type'       => 'success',
                        'attributes' => [
                            'href' => '/acp/auth/users/create',
                        ],
                    ],
                ],
            ],
        ];

        if ($this->request->wantsJson()) {
            return response(['data' => $data]);
        }

        return $this->view->make('ui::page', ['page' => $data]);
    }

    public function edit($id, Users $users, FormFactory $factory)
    {
        $user = $users->find($id);
        $form = $factory->fromJson('user.json');

        $this->dispatch(new MapForm($form, $user));
        $this->dispatch(new MakeFormInstance($form));

        $data = [
            'block' => [
                'component' => 'sv-form',
                'props'     => [
                    'fields' => $form->getFields(),
                    'config' => $form->getConfig(),
                ],
            ],
            'page'  => [
                'title'   => 'User Edit',
                'buttons' => [
                    [
                        'title'      => 'Users',
                        'button'     => 'index',
                        'type'       => 'success',
                        'attributes' => [
                            'href' => '/acp/auth/users/index',
                        ],
                    ],
                    [
                        'title'      => 'New User',
                        'button'     => 'create',
                        'type'       => 'success',
                        'attributes' => [
                            'href' => '/acp/auth/users/create',
                        ],
                    ],
                ],
            ],
        ];

        if ($this->request->wantsJson()) {
            return response(['data' => $data]);
        }

        return $this->view->make('ui::page', ['page' => $data]);
    }

    public function create(FormFactory $factory)
    {
        $form = $factory->fromJson('user.json');

        $this->dispatch(new MakeFormInstance($form));

        $data = [
            'block' => [
                'component' => 'sv-form',
                'props'     => [
                    'fields' => $form->getFields(),
                    'config' => $form->getConfig(),
                ],
            ],
            'page'  => [
                'title'   => 'User Create',
                'buttons' => [
                    [
                        'title'      => 'Users',
                        'button'     => 'index',
                        'type'       => 'success',
                        'attributes' => [
                            'href' => '/acp/auth/users/index',
                        ],
                    ],
                ],
            ],
        ];

        if ($this->request->wantsJson()) {
            return response(['data' => $data]);
        }

        return $this->view->make('ui::page', ['page' => $data]);
    }
}