<?php

namespace PrestaShop\Module\Souin\Form;

class AbstractField
{
    protected $name;
    protected $label;
    protected $value;

    /**
     * AbstractField constructor.
     * @param $name string
     * @param $label string
     * @param $initialValue mixed
     */
    public function __construct($name, $label, $initialValue = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->value = $initialValue;
    }

    public function getLabel()
    {
        return <<<HTML
<label for="{$this->name}">{$this->label}</label>
HTML;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setInitialValue($value) {
        $this->value = $value;
    }

    public function getField()
    {
        return '';
    }

    public function field_cb()
    {
        echo $this->getField();
    }

    public function renderField(): string
    {
        return <<<HTML
<div style="padding-top: 1rem;">
    {$this->getLabel()}
    {$this->getField()}
</div>
HTML;
    }
}
