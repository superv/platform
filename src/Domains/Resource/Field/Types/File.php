<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersFieldComposition;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Support\Composer\Composition;

class File extends FieldType implements DoesNotInteractWithTable, AcceptsEntry, AltersFieldComposition
{
    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    protected $requestFile;

    public function makeRules()
    {
        $rules = [];

        return Rules::make($rules)->merge(parent::makeRules())->get();
    }

    public function getValueForValidation()
    {
        return $this->requestFile;
    }

    public function getValue()
    {
        return null;
    }

    public function getConfig(): array
    {
        if ($entry = $this->entry) {
            $media = $this->makeMediaBag()->media()->where('label', $this->getName())->latest()->first();

            if ($media) {
                $this->setConfigValue('url', $media->url());
            }
        }

        return $this->config;
    }

    public function getMutator(): ?Closure
    {
        return function ($requestFile) {
            $this->requestFile = $requestFile;

            return function () {
                if (! $this->requestFile) {
                    return null;
                }

                $media = $this->makeMediaBag()
                              ->addFromUploadedFile($this->requestFile, $this->getConfigAsMediaOptions());

                if ($media) {
                    $this->setConfigValue('url', $media->url());
                }

                return $media;
            };
        };
    }

    protected function makeMediaBag(): MediaBag
    {
        return new MediaBag($this->entry, $this->getName());
    }

    protected function getConfigAsMediaOptions()
    {
        return MediaOptions::one()
                           ->disk($this->getConfigValue('disk', 'local'))
                           ->path($this->getConfigValue('path'))
                           ->visibility($this->getConfigValue('visibility', 'private'));
    }

    public function acceptEntry(EntryContract $entry)
    {
        $this->entry = $entry;
    }

    public function alterComposition(Composition $composition)
    {
        $config = $this->getConfig();

        $composition->replace('config', array_except($config, ['disk', 'path', 'visibility']));
    }
}