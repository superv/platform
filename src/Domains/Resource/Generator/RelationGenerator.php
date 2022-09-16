<?php

namespace SuperV\Platform\Domains\Resource\Generator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ReflectionClass;
use ReflectionMethod;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;

class RelationGenerator
{
    protected $model;

    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    public function __construct($modelClass)
    {
        $this->model = $modelClass;

        $this->reflection = new ReflectionClass($this->model);
    }

    public function make()
    {
        $methods = collect($this->reflection->getMethods())
            ->filter(function (ReflectionMethod $method) {
                return $method->class === $this->model;
            })
            ->map(function (ReflectionMethod $method) {
                return $this->parseRelation($method);
            })
            ->filter()
            ->keyBy(function (RelationConfig $config) {
                return $config->getName();
            });

//        dd($this->getCodeBlockForMethod($methods[0]));

        return $methods;
    }

    protected function parseRelation(ReflectionMethod $method)
    {
        $codeBlock = $this->getCodeBlockForMethod($method);

        if (! \Str::contains($codeBlock, ['$this->hasMany(', '$this->belongsTo('])) {
            return null;
        }

        $instance = $this->instance();

        $relation = $instance->{$method->getName()}();

        if ($relation instanceof HasMany) {
            return RelationConfig::create('has_many', [
                'name'          => $method->getName(),
                'related_model' => get_class($relation->getModel()),
                'foreign_key'   => $relation->getForeignKeyName(),
                'local_key'     => $relation->getLocalKeyName(),
            ]);
        }

        if ($relation instanceof BelongsTo) {
            return RelationConfig::create('belongs_to', [
                'name'          => $method->getName(),
                'related_model' => get_class($relation->getModel()),
                'foreign_key'   => $relation->getForeignKeyName(),
            ]);
        }
    }

    protected function getCodeBlockForMethod(ReflectionMethod $method)
    {
        $contents = file($this->reflection->getFileName());
        $index = -1;
        $code = '';
        foreach ($contents as $line) {
            if ($index++ < $method->getStartLine()) {
                continue;
            }
            if ($index + 2 > $method->getEndLine()) {
                break;
            }
            $code .= $line;
        }

        return $code;
    }

    protected function getImports()
    {
        $fp = fopen($this->reflection->getFileName(), 'r');

        $imports = [];
        while (! feof($fp)) {
            // get the line
            $buffer = fgets($fp);
            // break when class definition starts
            if (preg_match('/class\s+/i', $buffer)) {
                break;
            }
            $re = '/use\s+(((\w+\\\\)+)(\w+)\s*);/';
            if (preg_match($re, $buffer, $matches)) {
                $imports[] = $matches[1];
            }
        }

        return $imports;
    }

    protected function instance(): Model
    {
        return app($this->model);
    }
}