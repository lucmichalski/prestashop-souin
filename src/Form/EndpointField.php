<?php

namespace PrestaShop\Module\Souin\Form;

class EndpointField extends AbstractContainerFields
{
    protected static function getFields($basepath, $parent = null, $initialValue)
    {
        return [
            new BasePathField($basepath, $parent, $initialValue ? $initialValue->basepath : null),
            new BooleanField($parent ? \sprintf('%s[%s]', $parent, 'enable') : 'enable', 'Enable this endpoint?', (bool)$initialValue->enable),
        ];
    }
}
