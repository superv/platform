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

        $builder->build();

        $table = $builder->getTable();

        $data = [
            'block' => [
                'component' => 'sv-table',
                'props'     => [
                    'columns' => $table->getColumns(),
                    'rows'    => $table->getRows(),
                ],
            ],
            'page'  => [
                'title' => 'Users Index',
            ],
        ];

        if ($this->request->wantsJson()) {
            return response(['data' => [
                'block' => [
                    'component' => 'sv-table',
                    'props'     => [
                        'columns' => $table->getColumns(),
                        'rows'    => $table->getRows(),
                    ],
                ],
                'page'  => [
                    'title' => 'Users Index',
                ]]]);
        }

        return $this->view->make('ui::page', ['page' => $data]);
    }

    public function edit($id, Users $users, FormFactory $factory)
    {
        $user = $users->find($id);
        $form = $factory->fromJson('user.json');

        $this->dispatch(new MapForm($form, $user));
        $this->dispatch(new MakeFormInstance($form));

        return $this->view->make('ui::form', ['form' => $form]);
    }

    public function create(FormFactory $factory)
    {
        $form = $factory->fromJson('user.json');

        $this->dispatch(new MakeFormInstance($form));

        return $this->view->make('ui::form', ['form' => $form]);
    }
}