<?php

namespace PrestaShop\Module\Souin\Form;

class SelectField extends AbstractField
{
    protected $value = '';
    protected $options = [];

    public function __construct($name, $label, $parent = null, $initialValue)
    {
        parent::__construct($parent ? \sprintf('%s[%s]', $parent, $name) : $name, $label, $initialValue);
    }

    public function getField()
    {
        $fields = '';

        foreach ($this->options as $option) {
            $selected = $option === $this->value ? 'selected' : '';
            $fields .= <<<HTML
<option value="{$option}" {$selected}>{$option}</option>
HTML;
        }

        return <<<HTML
<select name="{$this->name}" id="{$this->name}">
    {$fields}
</select>
HTML;
    }
}
