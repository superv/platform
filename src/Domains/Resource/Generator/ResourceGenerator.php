<?php

namespace SuperV\Platform\Domains\Resource\Generator;

use SuperV\Platform\Platform;
use SuperV\Platform\Support\Parser;

class ResourceGenerator
{
    /**
     * @var \SuperV\Platform\Platform
     */
    protected $platform;

    /**
     * @var \SuperV\Platform\Support\Parser
     */
    protected $parser;

    /**
     * @var string
     */
    protected $target;

    public function __construct(Platform $platform, Parser $parser)
    {
        $this->platform = $platform;
        $this->parser = $parser;
    }

    private function getResourceConfig($table)
    {
        $config = [];

        $config[] = sprintf("\$config->label('%s');", str_unslug($table));
        $config[] = "\$config->nav('acp.app');";

        return implode(PHP_EOL.str_pad('', 4 * 4, ' '), $config);
    }

    private function getBlueprint($tableData)
    {
        $fields = [];
        $entryLabel = null;

        foreach ($tableData['fields'] as $key => $field) {
            $item = null;
            $fieldType = $field['type'];
            $column = $field['field'];
            if (in_array($fieldType, ['unique']) || is_array($column)) {
                continue;
            }
            $decorators = array_get($field, 'decorators', []);

            if (ends_with($column, '_id')) {
                $relation = str_replace_last('_id', '', $column);
                if ($relation !== 'parent') {
                    $related = str_plural($relation);
                    $method = in_array('nullable', $decorators) ? 'nullableBelongsTo' : 'belongsTo';
                    $item = sprintf("\$table->%s('%s', '%s', '%s');", $method, $related, $relation, $column);
                }
            }

            if (! $entryLabel && $field['type'] === 'string') {
                $decorators[] = 'entryLabel';
                $entryLabel = $field;
            }

            $field['decorators'] = $decorators;

            $fields[$key] = $item ?? $this->getItem($field);
        }

        return implode(PHP_EOL.str_pad('', 4 * 4, ' '), $fields);
    }

    public function withTableData($table, $tableData)
    {
        $template = file_get_contents($this->platform->resourcePath('stubs/generator/migration.stub'));

        $data = [
            'class_name'          => studly_case("create_{$table}_resource"),
            'table_name'          => $table,
            'migration_namespace' => 'sv_import',
            'resource'            => $table,
            'config'              => $this->getResourceConfig($table),
            'blueprint'           => $this->getBlueprint($tableData),
        ];
        $templateData = $this->parser->parse($template, $data);

        file_put_contents($this->target.'/'.date('Y_m_d_His').'_create_'.$table.'_resource.php', $templateData);

        return $templateData;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public static function make(): ResourceGenerator
    {
        return app(static::class);
    }

    protected function getItem(array $field)
    {
        $property = $field['field'];

        // If the field is an array,
        // make it an array in the Migration
        if (is_array($property)) {
            $property = "['".implode("','", $property)."']";
        } else {
            $property = $property ? "'$property'" : null;
        }

        $type = $field['type'];

        $output = sprintf(
            "\$table->%s(%s)",
            $type,
            $property
        );

        // If we have args, then it needs
        // to be formatted a bit differently
        if (isset($field['args'])) {
            $output = sprintf(
                "\$table->%s(%s, %s)",
                $type,
                $property,
                $field['args']
            );
        }
        if (isset($field['decorators'])) {
            $output .= $this->addDecorators($field['decorators']);
        }

        return $output.';';
    }

    /**
     * @param $decorators
     * @return string
     */
    protected function addDecorators($decorators)
    {
        $output = '';
        foreach ($decorators as $decorator) {
            $output .= sprintf("->%s", $decorator);
            // Do we need to tack on the parentheses?
            if (strpos($decorator, '(') === false) {
                $output .= '()';
            }
        }

        return $output;
    }
}
