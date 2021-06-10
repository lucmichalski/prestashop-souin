<?php

namespace PrestaShop\Module\Souin\Form;

class BasePathField extends TextField
{
    public function __construct($label = 'basepath', $parent = null, $initialValue)
    {
        parent::__construct($parent ? \sprintf('%s[%s]', $parent, 'basepath') : 'basepath', $label, $initialValue);
    }
}
