<?php

namespace SuperV\Platform\Domains\UI\Form;

use SuperV\Platform\Domains\Entry\EntryModel;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FieldType
{
    public static $fieldMap = [
        'text' => TextType::class,
        'textarea' => TextareaType::class,
        'integer' => IntegerType::class,
        'email' => EmailType::class,
        'choice' => ChoiceType::class,
        'relation' => ChoiceType::class,
    ];

    protected $field;

    protected $type;

    protected $rules;

    /**
     * @var array
     */
    private $config;

    /**
     * @var EntryModel
     */
    private $entry;

    public function __construct(EntryModel $entry, $field, $type, array $rules, array $config = [])
    {
        $this->field = $field;
        $this->type = $type;
        $this->rules = $rules;
        $this->config = $config;
        $this->entry = $entry;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return array_get(static::$fieldMap, $this->type, TextType::class);
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    public function getOptions()
    {
        $options = [];

        array_set($options, 'rules', $this->rules);
        if ($this->type == 'relation') {
            array_set($options, 'mapped', true);
            array_set($options, 'multiple', array_get($this->config, 'multiple', false));
            array_set($options, 'expanded', array_get($this->config, 'expanded', false));
            if ($related = array_get($this->config, 'related')) {
                if (method_exists($this->entry, $method = 'get'.studly_case($this->field).'Options')) {
                    $choices = $this->entry->{$method}()->pluck('id', 'name')->toArray();
                } else {
                    $related = new $related;
                    $choices = $related->newQuery()->pluck('id', $related->getTitleColumn())->toArray();
                }
                array_set($options, 'choices', $choices);
            }
        } else if ($this->type == 'choice') {
            array_set($options, 'choices', array_get($this->config, 'choices', []));

        }

        array_set($options, 'mapped', array_get($this->config, 'mapped', true));
        array_set($options, 'required', array_get($this->config, 'required', false));
        array_set($options, 'translation_domain', false);

        return $options;
    }
}
