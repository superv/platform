<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Support\Composer\Payload;

class SubFormField extends FieldType
{
    protected $formData;

    protected $entryId;

    protected function boot()
    {
        $this->field->on('form.composing', $this->composer());
    }

    public function saving(FormInterface $form)
    {
//        sv_debug('saving', $this->formData);
    }

    public function resolveValueFromRequest(Request $request, ?EntryContract $entry = null)
    {
        $this->formData = $request->all()[$this->getColumnName()];


        return null;
    }

    public function resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null)
    {
        return parent::resolveDataFromRequest($data, $request, $entry);
    }

    /**
     * @return mixed
     */
    public function getFormData()
    {
        return $this->formData;
    }

    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    protected function composer()
    {
        return function (Payload $payload) {
            $formUrl = $this->getConfigValue('form');
            if ($this->entryId) {
                $formUrl .= '/'.$this->entryId;
            }
            $payload->set('config.form', $formUrl);
            $payload->set('meta.full', true);
        };
    }
}