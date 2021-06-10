<?php

namespace PrestaShop\Module\Souin\Form;

class SouinEndpoint extends EndpointField
{
    public function __construct($parent = null, $initialValue)
    {
        parent::__construct(
            array_merge(
                self::getFields('Souin base path', $parent, $initialValue),
                [
                    new BooleanField(\sprintf('%s[security]', $parent), 'Secure this endpoint?', (bool)$initialValue->security),
                ]
            ),
            'souin_api_souin_configuration',
            'Souin API cache configuration'
        );
    }
}
