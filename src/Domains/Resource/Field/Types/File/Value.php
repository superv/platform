<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\File;

use SplFileInfo;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;
use SuperV\Platform\Domains\Resource\Field\FieldValue;

class Value extends FieldValue
{
    /**
     * @var \SplFileInfo|null
     */
    protected $requestFile;

    public function resolveeeeee(): FieldValueInterface
    {
        parent::resolve();

        if ($this->request) {
            $this->value = function (EntryContract $entry) {
                if (! $file = $this->request->file($this->getFieldHandle())) {
                    return null;
                }

                if (! $file instanceof SplFileInfo) {
                    return null;
                }

                $bag = new MediaBag($entry, $this->getFieldHandle());

                $media = $bag->addFromUploadedFile($file, $this->field->getFieldType()->getConfigAsMediaOptions());

                return $media;
            };
        }

        return $this;
    }

    protected function resolveRequest(): void
    {
        parent::resolveRequest();

        $this->value = function (EntryContract $entry) {
            if (! $file = $this->request->file($this->getFieldHandle())) {
                return null;
            }

            if (! $file instanceof SplFileInfo) {
                return null;
            }

            $bag = new MediaBag($entry, $this->getFieldHandle());

            $media = $bag->addFromUploadedFile($file, $this->field->getFieldType()->getConfigAsMediaOptions());

            return $media;
        };
    }
}