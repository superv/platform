<?php

namespace Tests\Platform\Domains\Resource\Form\v2\Helpers;

use Closure;
use Mockery;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface;
use SuperV\Platform\Domains\Resource\Form\v2\FormFactory;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Exceptions\ValidationException;

class FormFake extends \SuperV\Platform\Domains\Resource\Form\v2\Form
{
    protected $identifier = 'test_form_id';

    protected $url = 'url-to-form';

    protected $method = 'PATCH';

    protected $fakeEntries;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface
     */
    protected $repositoryMock;

    public function createFormEntry($formName = 'default')
    {
        try {
            /** @var FormModel $formEntry */
            $formEntry = FormModel::create([
                'uuid'       => uuid(),
                'title'      => 'Fake Form',
                'identifier' => $this->getIdentifier().'.forms:'.$formName,
                'name'       => $formName,
            ]);
            /** @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormField $field */
            foreach ($this->getFields() as $field) {
                $formEntry->createField([
                    'identifier' => $field->getIdentifier(),
                    'name'       => $field->getName(),
                    'type'       => $field->getType(),
                ]);
            }
        } catch (ValidationException $e) {
            PlatformException::throw($e);
        }

        return $formEntry;
    }

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

            if (! str_contains($identifier, '.fields:')) {
                $identifier = 'sv.user.fields:'.$identifier;
            }

            $this->fields->addField(
                FormFieldFake::fake($identifier, array_merge($params,
                    [
                        'name' => explode('.fields:', $identifier)[1],
                    ]))
            );
        }

        return $this;
    }

    public static function fake(Closure $callback = null): FormFake
    {
        app()->instance(EntryRepositoryInterface::class, $repoMock = Mockery::mock(EntryRepositoryInterface::class));
        app()->bind(FormInterface::class, FormFake::class);

        $builder = FormFactory::createBuilder();
        $builder->setFormIdentifier('test_form_id');

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
