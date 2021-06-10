<?php

namespace PrestaShop\Module\Souin\Form;

class Port extends AbstractContainerFields
{
    public function __construct($parent = null, $initialValue)
    {
        parent::__construct([
            new NumberField($parent ? \sprintf('%s[web]', $parent) : 'web', 'HTTP port', $initialValue ? $initialValue->web : null),
            new NumberField($parent ? \sprintf('%s[tls]', $parent) : 'tls', 'TLS port', $initialValue ? $initialValue->tls : null),
        ], 'souin_default_cache_port_configuration', 'Souin Default Cache Port configuration');
    }
}
