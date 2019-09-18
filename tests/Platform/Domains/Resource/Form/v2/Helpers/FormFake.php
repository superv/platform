<?php

namespace Tests\Platform\Domains\Resource\Form\v2\Helpers;

use Closure;
use Mockery;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface;
use SuperV\Platform\Domains\Resource\Form\v2\FormFactory;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;

class FormFake extends \SuperV\Platform\Domains\Resource\Form\v2\Form
{
    protected $identifier = 'form-id';

    protected $url = 'url-to-form';

    protected $method = 'PATCH';

    protected $fakeEntries;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface
     */
    protected $repositoryMock;

    public function setFakeFields(array $fakeFields): FormFake
    {
        $this->fields = FormFieldCollection::make();

        foreach ($fakeFields as $key => $val) {
            if (is_numeric($key)) {
                $identifier = $val;
                $params = ['type' => 'text'];
            } elseif (is_array($val)) {
                $identifier = $key;
                $params = $val;
            } elseif (is_string($val)) {
                $identifier = $key;
                $params = ['type' => $val];
            } else {
                continue;
            }

            $this->fields->addField(FormFieldFake::fake($identifier, $params));
        }

        return $this;
    }

    public static function fake(Closure $callback = null): FormFake
    {
        app()->instance(EntryRepositoryInterface::class, $repoMock = Mockery::mock(EntryRepositoryInterface::class));
        app()->bind(FormInterface::class, FormFake::class);

        $builder = FormFactory::createBuilder();
        $builder->setFormIdentifier('the-form-id');

        /** @var self $form */
        $form = $builder->getForm();

        $form->setRepositoryMock($repoMock);

        if ($callback) {
            $callback($repoMock);
        }

        return $form;
    }

    protected function setRepositoryMock(EntryRepositoryInterface $repositoryMock)
    {
        return $this->repositoryMock = $repositoryMock;
    }
}
