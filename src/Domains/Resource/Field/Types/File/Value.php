<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\File;

use SplFileInfo;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldValue;

class Value extends FieldValue
{
    /**
     * @var \SplFileInfo|null
     */
    protected $requestFile;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Types\File\Uploader
     */
    protected $manager;

    public function __construct(Uploader $manager)
    {
        $this->manager = $manager;
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

            $this->manager->setEntry($entry)
                          ->setLabel($this->getFieldHandle())
                          ->setUploadedFile($file)
                          ->setOptions($this->field->type()->getConfigAsMediaOptions());

            return $this->manager->save();
        };
    }
}