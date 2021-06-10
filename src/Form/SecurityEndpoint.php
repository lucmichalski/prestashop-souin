<?php

namespace PrestaShop\Module\Souin\Form;

class SecurityEndpoint extends EndpointField
{
    public function __construct($parent = null, $initialValue)
    {
        $section = 'souin_api_security_configuration';

        parent::__construct(
            array_merge(
                self::getFields('Authentication base path', $parent, $initialValue),
                [
                    new TextField($parent ? \sprintf('%s[%s]', $parent, 'secret') : 'secret', 'Security key', $initialValue ? $initialValue->secret : null),
                    new RepeatableField(\sprintf('%s[%s]', $parent, 'users'), 'Credentials to access the Souin API', $initialValue ? $initialValue->users : null, [
                        new TextField(\sprintf('%s[%s][][username]', $parent, 'users'), 'Username'),
                        new TextField(\sprintf('%s[%s][][password]', $parent, 'users'), 'Password'),
                        //new RepeatableField(\sprintf('%s[ykeys][][headers]', $base), 'Headers to restrict tag', null, [
                        //    new TextField(\sprintf('%s[ykeys][][headers][]', $base), 'Header to tag as using this one'),
                        //])
                    ]),
                ]
            ),
            $section,
            'Souin API security configuration'
        );
    }
}
