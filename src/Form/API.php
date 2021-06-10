<?php

namespace PrestaShop\Module\Souin\Form;

class API extends AbstractContainerFields
{
    public function __construct($initialValue)
    {
        $parent = 'configuration[api]';

        parent::__construct([
            new BasePathField('Souin API base path', $parent, $initialValue ? $initialValue->basepath : null),
            new SecurityEndpoint(\sprintf('%s[security]', $parent), $initialValue ? $initialValue->security : null),
            new SouinEndpoint(\sprintf('%s[souin]', $parent), $initialValue ? $initialValue->souin : null),
        ], 'souin_api_configuration', 'Souin API configuration');
    }
}
