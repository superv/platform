<?php namespace SuperV\Platform\Domains\UI\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FieldType
{
    public static $fieldMatrix = [
        'text'     => TextType::class,
        'textarea' => TextareaType::class,
        'integer'  => IntegerType::class,
        'email'    => EmailType::class,
        'choice'   => ChoiceType::class,
        'relation' => ChoiceType::class,
    ];

    protected $field;

    protected $type;

    protected $rules;

    /**
     * @var array
     */
    private $config;

    public function __construct($field, $type, array $rules, array $config = [])
    {
        $this->field = $field;
        $this->type = $type;
        $this->rules = $rules;
        $this->config = $config;
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
        return array_get(static::$fieldMatrix, $this->type, TextType::class);
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
        array_set($options, 'mapped', array_get($this->config, 'mapped', true));

        array_set($options, 'rules', $this->rules);
        if ($this->type == 'relation') {
            array_set($options, 'mapped', false);
            if ($related = array_get($this->config, 'related')) {
                $choices = $related::pluck('id', 'name')->toArray();
                array_set($options, 'choices', $choices);
            }
        }

        if ($this->type == 'relation') {
            array_set($options, 'multiple', array_get($this->config, 'multiple', false));
            array_set($options, 'expanded', array_get($this->config, 'expanded', false));
        }

        return $options;
    }
}