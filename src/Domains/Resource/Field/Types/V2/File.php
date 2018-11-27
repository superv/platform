<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\V2;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\Types\FieldTypeV2;
use SuperV\Platform\Support\Composer\Composition;

class File extends FieldTypeV2 implements DoesNotInteractWithTable
{
    protected $requestFile;

    public function getMedia(EntryContract $entry, $label): ?Media
    {
        $bag = new MediaBag($entry, $label);

        return $bag->media()->where('label', $label)->latest()->first();
    }

    public function getComposer(): ?Closure
    {
        return function (Composition $composition, EntryContract $entry) {
            if ($media = $this->getMedia($entry, $this->getName())) {
                $composition->replace('image_url', $media->getUrl());
                $composition->replace('config', null);
            }
        };
    }

    public function getMutator(): ?Closure
    {
        return function ($requestFile, EntryContract $entry) {
            $this->requestFile = $requestFile;

            return function () use ($entry) {
                if (! $this->requestFile || ! $entry) {
                    return null;
                }

                $bag = new MediaBag($entry, $this->getName());

                $media = $bag->addFromUploadedFile($this->requestFile, $this->getConfigAsMediaOptions());

                return $media;
            };
        };
    }

    protected function getConfigAsMediaOptions()
    {
        return MediaOptions::one()
                           ->disk($this->getConfigValue('disk', 'local'))
                           ->path($this->getConfigValue('path'))
                           ->visibility($this->getConfigValue('visibility', 'private'));
    }

}