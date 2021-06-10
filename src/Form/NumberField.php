<?php

namespace PrestaShop\Module\Souin\Form;

class NumberField extends AbstractField
{
    protected $value = 0;

    public function getField()
    {
        return <<<HTML
<div>
  <input type="number" id="{$this->name}" name="{$this->name}" value="{$this->value}" />
</div>
HTML;
    }
}
