<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\File;

use Closure;
use Illuminate\Database\Query\JoinClause;
use SplFileInfo;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Field\Contracts\DecoratesFormComposer;
use SuperV\Platform\Domains\Resource\Field\Contracts\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\FieldRules;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Support\Composer\Payload;

class FileType extends FieldType implements
    DoesNotInteractWithTable,
    HasModifier,
    SortsQuery,
    DecoratesFormComposer
{
    protected $handle = 'file';

    protected $component = 'sv_file_field';

    protected $requestFile;

    protected function boot()
    {
//        $this->field->on('form.composing', $this->composer());

        $this->field->on('view.composing', $this->composer());
        $this->field->on('table.composing', $this->composer());
    }

    public function updateRules(FieldRules $rules)
    {
        if (is_null($this->requestFile)) {
            $rules->removeAll();
        }

    }

    public function getModifier(): Closure
    {
        return function ($value, ?EntryContract $entry) {
            $this->requestFile = $value instanceof SplFileInfo ? $value : null;

            return function () use ($entry) {
                if (! $this->requestFile || ! $entry) {
                    return null;
                }

                if (! $this->requestFile instanceof SplFileInfo) {
                    return null;
                }

                $bag = new MediaBag($entry, $this->getFieldHandle());

                $media = $bag->addFromUploadedFile($this->requestFile, $this->getConfigAsMediaOptions());

                return $media;
            };
        };
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param                                       $direction
     * @throws \Exception
     */
    public function sortQuery($query, $direction)
    {
        $model = $query->getModel();

        $query->getQuery()->leftJoin('sv_media', function (JoinClause $join) use ($model) {
            $join->on('sv_media.id', '=', $model->getQualifiedKeyName());
            $join->where('sv_media.owner_type', '=', $this->field->identifier()->parent());
        });

        $query->orderBy('sv_media.size', $direction);
    }

    public static function getMedia(EntryContract $entry, $label): ?Media
    {
        $bag = new MediaBag($entry, $label);

        $query = $bag->media()->where('label', $label)->latest();

        return $query->first();
    }

    protected function composer()
    {
        return function (Payload $payload, ?EntryContract $entry) {
            if (! $entry) {
                return;
            }
            if ($media = $this->getMedia($entry, $this->getFieldHandle())) {
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

    public function getFormComposerDecoratorClass()
    {
        return FormComposer::class;
    }
}
