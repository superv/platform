<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Field\Field;

class File extends Field
{
    protected $hasColumn = false;

    public function getValue()
    {
        return null;
    }

    public function getConfig(): array
    {
        if ($entry = $this->getResourceEntry()) {
            $media = $this->makeMediaBag()->media()->where('label', $this->getName())->latest()->first();

            if ($media) {
                $this->setConfigValue('url', $media->url());
            }
        }

        return $this->config;
    }

    public function setValue($requestFile): ?Closure
    {
        return function () use ($requestFile) {
            if (! $requestFile) {
                return null;
            }

            $media = $this->makeMediaBag()
                          ->addFromUploadedFile($requestFile, $this->getConfigAsMediaOptions());

            if ($media) {
                $this->setConfigValue('url', $media->url());
            }

            return $media;
        };
    }

    protected function makeMediaBag(): MediaBag
    {
        return new MediaBag($this->getResourceEntry(), $this->getName());
    }

    protected function getConfigAsMediaOptions()
    {
        return MediaOptions::one()
                           ->disk($this->getConfigValue('disk', 'local'))
                           ->path($this->getConfigValue('path'))
                           ->visibility($this->getConfigValue('visibility', 'private'));
    }
}