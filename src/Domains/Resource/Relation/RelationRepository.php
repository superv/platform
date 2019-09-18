<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Jobs\CreatePivotTable;
use SuperV\Platform\Domains\Resource\ResourceModel;

class RelationRepository
{
    public function create(ResourceModel $resource, RelationConfig $config, Blueprint $blueprint, bool $isRequired)
    {
        $relationType = Relation::resolve($config->getType());

        if ($config->hasPivotTable()) {
            (new CreatePivotTable)($config);
        }

        $resource->resourceRelations()->create([
            'uuid'   => uuid(),
            'name'   => $config->getName(),
            'type'   => $config->getType(),
            'config' => $config->toArray(),
        ]);

        if ($config->type()->isMorphTo()) {
            $name = $config->getName();

            $blueprint->addPostBuildCallback(
                function (Blueprint $blueprint) use ($isRequired, $name) {
                    $blueprint->string("{$name}_type")->setRequired($isRequired);

                    $blueprint->unsignedBigInteger("{$name}_id")->setRequired($isRequired);
                    $blueprint->index(["{$name}_type", "{$name}_id"]);
                }
            );

            $morphToField = $resource->makeField($name);
            $morphToField->fill([
                'type'   => 'morph_to',
                'config' => RelationConfig::morphTo()
                                          ->relationName($name)
                                          ->toArray(),
                'flags'  => ['nullable'],
            ]);
            $morphToField->save();
        }

        return $relationType;
    }

    /** * @return static */
    public static function make()
    {
        return app(static::class);
    }
}
