<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\V2;

use Closure;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersFieldComposition;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\Types\FieldTypeV2;
use SuperV\Platform\Support\Composer\Composition;

class File extends FieldTypeV2 implements DoesNotInteractWithTable, AltersFieldComposition
{
    protected $requestFile;

    public function getValueForValidation()
    {
        return $this->requestFile;
    }

    public function getValue()
    {
        return null;
    }

    public function getMediaUrl()
    {
        $media = $this->makeMediaBag()->media()->where('label', $this->getName())->latest()->first();

        if (! $media) {
            return null;
        }

        return $media->url();
    }

    public function getMutator(): ?Closure
    {
        if (! $this->field->hasEntry()) {
            return null;
        }

        return function ($requestFile) {
            $this->requestFile = $requestFile;

            return function () {
                if (! $this->requestFile) {
                    return null;
                }

                $media = $this->makeMediaBag()
                              ->addFromUploadedFile($this->requestFile, $this->getConfigAsMediaOptions());

                if ($media) {
                    $this->field->setConfigValue('url', $media->url());
                }

                return $media;
            };
        };
    }

    protected function makeMediaBag(): MediaBag
    {
        return new MediaBag($this->field->getEntry(), $this->getName());
    }

    protected function getConfigAsMediaOptions()
    {
        return MediaOptions::one()
                           ->disk($this->getConfigValue('disk', 'local'))
                           ->path($this->getConfigValue('path'))
                           ->visibility($this->getConfigValue('visibility', 'private'));
    }

    public function alterComposition(Composition $composition)
    {
        if ($this->field->hasEntry()) {
            $composition->replace('config', ['url' => $this->getMediaUrl()]);
        }
    }
}