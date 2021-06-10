<?php

namespace PrestaShop\Module\Souin\Form;

class BooleanField extends AbstractField
{
    protected $value = false;

    public function getField()
    {
        $value = (bool)$this->value ? 'checked ' : '';
        return <<<HTML
<div>
  <input type="checkbox" id="{$this->name}" name="{$this->name}" {$value} />
  <label for="{$this->name}">{$this->label}</label>
</div>
HTML;
    }
}
