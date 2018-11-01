<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;

class FileField extends FieldType
{
    protected $type = 'file';

    public function getValue()
    {
        return null;
    }

    public function setValue($requestFile): ?Closure
    {
        if (! $requestFile) {
            return null;
        }

        return function () use ($requestFile) {
            (new MediaBag($this->getResourceEntry(), $this->getName()))
                ->addFromUploadedFile($requestFile, $this->getConfigAsMediaOptions());
        };
    }

    public function getConfig(): array
    {
        $config = parent::getConfig();
        if ($entry = $this->getResourceEntry()) {
            $mediaQuery = (new MediaBag($this->getResourceEntry(), $this->getName()))->media();
            $media = $mediaQuery->where('label', $this->getName())->latest()->first();

            $media ? $config['url'] = $media->url() : null;
        }

        return $config;
    }

    protected function getConfigAsMediaOptions()
    {
        return MediaOptions::one()
                           ->disk($this->getConfigValue('disk', 'local'))
                           ->path($this->getConfigValue('path'))
                           ->visibility($this->getConfigValue('visibility', 'private'));
    }
}