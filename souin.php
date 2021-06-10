<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\Module\Souin\Form\SouinConfiguration;
use PrestaShop\Module\Souin\Install\Installer;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

class Souin extends \Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'souin';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Luc Michalski';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Souin - Cache');
        $this->description = $this->l('High performance caching solution using Souin');

        $this->confirmUninstall = $this->l('Are you sure you don\'t want to manage Souin logic from the Prestashop administration panel ?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->registerHook('actionOtherPageForm');
        $this->registerHook('displayBackOfficeHeader');
    }

    public function toObject($array)
    {
        $obj = new stdClass;
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = $this->toObject($v);
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }

    public function install()
    {
        $installer = new Installer();
        return $installer->install($this);
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $installer = new Installer();

        return $installer->uninstall() && parent::uninstall();
    }

    function parseToYAML($configuration)
    {
        $yml = '';

        $enabledSecurity = (bool)$configuration->api->security->enable;
        $enabledSouin = (bool)$configuration->api->souin->enable;
        $isSecureSouin = (bool)$configuration->api->souin->security;
        $yml .= <<<YAML
api:
  basepath: {$configuration->api->basepath}
  security:
    basepath: {$configuration->api->security->basepath}
    enable: $enabledSecurity
    secret: {$configuration->api->security->secret}

YAML;

        if (\count($configuration->api->security->users) > 0) {
            $yml .= <<<YAML
    users:

YAML;

            foreach ($configuration->api->security->users as $user) {
                if (!$user->username || !$user->password) {
                    continue;
                }
                $yml .= <<<YAML
    - username: {$user->username}
      password: {$user->password}
YAML;
            }
        }

        $yml .= <<<YAML

  souin:
    basepath: {$configuration->api->souin->basepath}
    enable: $enabledSouin
    security: $isSecureSouin
default_cache:
  port:
    web: {$configuration->default_cache->port->web}
    tls: {$configuration->default_cache->port->tls}
  regex:
    exclude: {$configuration->default_cache->regex->exclude}
  ttl: {$configuration->default_cache->ttl}
log_level: {$configuration->log_level}
reverse_proxy_url: {$configuration->reverse_proxy_url}

YAML;

        if (\count($configuration->ykeys) > 0) {
            $yml .= <<<YAML
ykeys:

YAML;
            foreach ($configuration->ykeys as $ykey) {
                if (!$ykey->name) {
                    continue;
                }
                $yml .= <<<YAML
  {$ykey->name}:
    url: {$ykey->url}
YAML;
            }
        }

        return $yml;
    }

    function parseRepeatableFields($haystack, $length = 2)
    {
        $stack = [];
        for ($i = 0; $i < \count($haystack) / $length; $i++) {
            $stack[] = (object)array_merge(
                $haystack[($i * $length)],
                $haystack[($i * $length) + 1] ?: []
            );
        }
        return $stack;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $db = \Db::getInstance();
        $table_name = _DB_PREFIX_.'souin';

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)\Tools::isSubmit('submitSouinModule')) == true) {
            if (isset($_POST['configuration'])) {
                $c = $_POST['configuration'];

                $c['api']['security']['users'] = $this->parseRepeatableFields($c['api']['security']['users']);
                $c['ykeys'] = $this->parseRepeatableFields($c['ykeys']);
                $souin = $this->toObject($c);
                $configuration = new SouinConfiguration($souin);
                \file_put_contents('/app/souin.yml', $this->parseToYAML($souin));

                $db->update(
                    $table_name,
                    [
                        'configuration' => \base64_encode(\serialize($configuration)),
                    ],
                    'id = 1',
                    0,
                    true,
                    false,
                    false
                );
            }
        }

        $result = $db->getRow(<<<SQL
SELECT * FROM {$table_name} WHERE id = 1
SQL
        )['configuration'];
        $souin = \unserialize(\base64_decode($result));
        if (!$souin) {
            $souin = new SouinConfiguration();
        }
        $this->context->smarty->assign('souinConfiguration', $souin->renderField());
        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }

        $this->context->controller->addJquery();
        $this->context->controller->addJqueryUI(['ui.core', 'ui.sortable']);
        $this->context->controller->addJS([$this->_path.'views/js/repeatable-fields.js', $this->_path.'views/js/repeatable-init.js']);
    }
}
