<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Support\Composer\Payload;

class FileField extends FieldType implements DoesNotInteractWithTable, HasModifier
{
    protected $requestFile;

    protected function boot()
    {
        $this->field->on('form.composing', $this->composer());
//        $this->>field->on('form.mutating', $this->mutator());

        $this->field->on('view.composing', $this->composer());
        $this->field->on('table.composing', $this->composer());
    }

    public function getMedia(EntryContract $entry, $label): ?Media
    {
        $bag = new MediaBag($entry, $label);

        $query = $bag->media()->where('label', $label)->latest();

        return $query->first();
    }

    public function getModifier(): Closure
    {
        return function ($value, ?EntryContract $entry) {
            $this->requestFile = $value;

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

    protected function composer()
    {
        return function (Payload $payload, ?EntryContract $entry) {
            if (! $entry) {
                return;
            }
            if ($media = $this->getMedia($entry, $this->getName())) {
                $payload->set('image_url', $media->getUrl());
                $payload->set('config', null);
            }
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
