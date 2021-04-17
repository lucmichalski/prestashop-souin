<?php
/**
 * Souin Cache powered by Luc Michalski
 *
 *    @author    Luc Michalski
 *    @copyright 2021 Evolutive Group
 *    @license   You are just allowed to modify this copy for your own use. You must not souintribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

define('SOUIN_BACKWARD_COMPATIBILITY', dirname(__FILE__) . '/classes/BackwardCompatibility.php');

class Souin extends Module
{
    const MODE_SIMPLE = 'simple';
    const MODE_ADVANCED = 'advanced';

    protected $mode = self::MODE_SIMPLE;

    public static function loadDependencies()
    {
        $dependencies = [
            SOUIN_BACKWARD_COMPATIBILITY,
        ];

        foreach ($dependencies as $dependency) {
            include_once $dependency;
        }
    }

    /**
     * Initialize module.
     */
    public function __construct()
    {
        $this->name = 'souin';
        $this->tab = 'others';
        $this->version = '1.1.1';
        $this->author = 'Luc Michalski';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->secure_key = md5(uniqid(rand(), true));


        $this->ps_versions_compliancy = array(
            'min' => '1.6.0.4',
        );

        parent::__construct();

        self::loadDependencies();

        $this->displayName = $this->l('Souin Cache');

        $this->description
        = $this->l('High performance caching solution using Souin');

        // $this->module_key = '19f1cdba1d2ca5a7557f8c4322031a26';
    }

    /**
     * Handles some backward compatibility cases.
     */
    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        }

        return BackwardCompatibility::undefinedMethod($method, $args);
    }

    /**
     * Installs souin cache in Prestashop.
     *
     * @see    Module::install()
     * @return bool The status of the installation process
     */
    public function install()
    {
        if ($this->isAlreadyInstalled()) {
            return true;
        }

        return (
            parent::install()
            && $this->installDb()
            && $this->installHooks()
            && $this->installTab()
        );
    }

    /**
     * Provides the install hooks.
     *
     * @return bool
     */
    protected function installHooks()
    {
        if ($backwardCompatibilityHooks = BackwardCompatibility::installHooks($this)) {
            return $backwardCompatibilityHooks;
        }

        return $this->registerHook("actionDispatcherBefore");
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminSouincache';
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Evolutive Souin Cache';
        }

        $tab->id_parent = (int)Tab::getIdFromClassName('AdminAdminPreferences');
        $tab->module = $this->name;

        return $tab->add();
    }

    /**
     * Installs the dabase table.
     *
     * @return void
     */
    public function installDb()
    {
        $return = true;
        $sql = array();

        include dirname(__FILE__) . '/sql_install.php';

        foreach ($sql as $s) {
            $return &= Db::getInstance()->execute($s);
        }

        return $return;
    }

    /**
     * Uninstalls souin cache from Prestashop.
     *
     * @see    Module::uninstall()
     * @return bool The status of the uninstallation process
     */
    public function uninstall()
    {
        return parent::uninstall()
        && $this->uninstallTab()
        && $this->uninstallHooks()
        && $this->uninstallDb();
    }

    /**
     * Removes the tab.
     *
     * @return void
     */
    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminSouincache');
        if ($id_tab) {
            $tab = new Tab($id_tab);

            return $tab->delete();
        }

        return false;
    }

    /**
     * Uninstalls the database tables.
     *
     * @return bool
     */
    public function uninstallDb()
    {
        $sql = array();

        include dirname(__FILE__) . '/sql_install.php';

        $tables = array_keys($sql);

        foreach ($tables as $name) {
            Db::getInstance()->execute('DROP TABLE IF EXISTS ' . $name);
        }

        return true;
    }

    /**
     * Provides the uninstall hooks.
     *
     * @return mixed
     */
    protected function uninstallHooks()
    {
        if ($backwardCompatibilityHooks = BackwardCompatibility::uninstallHooks($this)) {
            return $backwardCompatibilityHooks;
        }

        return $this->unregisterHook("actionDispatcherBefore");
    }

    /**
     * Enables the tab on module enable.
     *
     * @param boolean $force_all
     * @return bool
     */
    public function enable($force_all = false)
    {
        return (
            parent::enable($force_all)
            && Tab::enablingForModule($this->name)
        );
    }

    /**
     * Disables the tab on module disable.
     *
     * @param boolean $force_all
     * @return bool
     */
    public function disable($force_all = false)
    {
        return (
            parent::disable($force_all)
            && Tab::disablingForModule($this->name)
        );
    }

    /**
     * Verifies if an update is being processed.
     *
     * @return boolean
     */
    public function isUpdating()
    {
        $db_version = Db::getInstance()->getValue('SELECT `version` FROM `'
        . _DB_PREFIX_ . 'module` WHERE `name` = \'' . pSQL($this->name) . '\'');

        return version_compare($this->version, $db_version, '>');
    }

    /**
     *
     */
    public function isAlreadyInstalled()
    {
        if (Db::getInstance()->getValue('SELECT `id_module` FROM `'
        . _DB_PREFIX_ . 'module` WHERE name =\'' . pSQL($this->name) . '\'')) {
            return true;
        }
        return false;
    }

    /**
     * Builds and handles the module configuration form
     *
     * @see    Module::getContent()
     * @return string The output of the configartion page
     */
    public function getContent()
    {
        return $this->getConfigForm();
    }

    /**
     * Creates the configuration form for the module
     *
     * @return array The module's configuration form
     */
    public function getConfigForm()
    {

        $connection_configuration = array();
        $database_options = array();
        $fpc = array();
        // $session_cache = array();

        $connection_inputs = array(
            'enable_cache' => array(
                'type' => 'switch',
                'label' => $this->l('Enable Souin Cache'),
                'name' => 'PS_SOUIN_STATUS',
                'desc' =>
                $this->l(
                    'Enables Souin as a cache backend.'
                ),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            ),
            'cache_namespace_filters' => array(
                'type' => 'text',
                'label' => $this->l('Cache namespace filters (separated by commas ",")'),
                'name' => 'PS_SOUIN_NAMESPACE_FILTERS',
                'class' => 'col-sm-120',
            ),
        );

        $this->messageConfigurationInputs($connection_inputs);

        $connection_configuration['form'] = array(
            'legend' => array(
                'title' => $this->l('Souin connection'),
                'icon' => 'icon-cogs',
            ),
            'input' => $connection_inputs,
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $fpc_inputs = array(
            'cache_index_page' => array(
                'type' => 'switch',
                'label' => $this->l('Cache Home Page'),
                'name' => 'PS_SOUIN_CACHE_OBJECT_INDEX',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            ),
            'cache_product_pages' => array(
                'type' => 'switch',
                'label' => $this->l('Cache Product Pages'),
                'name' => 'PS_SOUIN_CACHE_OBJECT_PRODUCTS',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            ),
            'cache_category_pages' => array(
                'type' => 'switch',
                'label' => $this->l('Cache Category Pages'),
                'name' => 'PS_SOUIN_CACHE_OBJECT_CATEGORIES',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            ),
            'cache_cms_pages' => array(
                'type' => 'switch',
                'label' => $this->l('Cache CMS Pages'),
                'name' => 'PS_SOUIN_CACHE_OBJECT_CMS',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            ),
            'cache_contact_page' => array(
                'type' => 'switch',
                'label' => $this->l('Cache Contact Page'),
                'name' => 'PS_SOUIN_CACHE_OBJECT_CONTACT',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            ),
            'cache_stores_page' => array(
                'type' => 'switch',
                'label' => $this->l('Cache Stores Page'),
                'name' => 'PS_SOUIN_CACHE_OBJECT_STORES',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            ),
            'cache_sitemap_page' => array(
                'type' => 'switch',
                'label' => $this->l('Cache Sitemap Page'),
                'name' => 'PS_SOUIN_CACHE_OBJECT_SITEMAP',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            ),
        );

        $fpc['form'] = array(
            'legend' => array(
                'title' => $this->l('Full-Page Cache & Database Caching Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => $fpc_inputs,
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang
        = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
        Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_' . $this->name;
        $helper->currentIndex
        = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name
        . '&tab_module=' . $this->tab
        . '&module_name=' . $this->name;

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            // 'fields_value' => RedisHelper::getConfig(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($connection_configuration, $fpc));
    }

    /**
     *
     */
    protected function isControllerCacheEnabled($controller = false)
    {
        if (!$controller) {
            $controller = Dispatcher::getInstance()->getController();
        }

        /**
         * We don't allow serving admin pages from cache.
         */
        if ($controller == 'AdminModules') {
            return false;
        }

        $controllers_cache_map = array(
            'product' => 'PS_SOUIN_CACHE_OBJECT_PRODUCTS',
            'cms' => 'PS_SOUIN_CACHE_OBJECT_CMS',
            'category' => 'PS_SOUIN_CACHE_OBJECT_CATEGORIES',
            'contact' => 'PS_SOUIN_CACHE_OBJECT_CONTACT',
            'stores' => 'PS_SOUIN_CACHE_OBJECT_STORES',
            'sitemap' => 'PS_SOUIN_CACHE_OBJECT_SITEMAP',
            'index' => 'PS_SOUIN_CACHE_OBJECT_INDEX',
        );

        if (isset($controllers_cache_map[$controller])) {
            $config_key = $controllers_cache_map[$controller];
            return $config_key;
        }

        return false;
    }

    protected function messageConfigurationInputs(&$input_elements)
    {
        foreach ($input_elements as $key => $element) {
            if ($this->mode == self::MODE_SIMPLE
                && (isset($element['conf_type']) && $element['conf_type'] == self::MODE_ADVANCED)) {
                unset($input_elements[$key]);
            }

            if (isset($element['dependency']['souin']) && $element['dependency']['souin'] && !$this->souin) {
                unset($input_elements[$key]);
            }
        }
    }

    /**
     * Backward Compatibility Hook
     * PS1.6
     */
    public function hookActionDispatcher()
    {
        if (!BackwardCompatibility::versionCheck('1.6.0', '1.6.9')) {
            return;
        }
        $this->hookActionDispatcherBefore();
    }

    /**
     * If a cached page exists for the current request
     * return it and abort
     * PS1.7+
     */
    public function hookActionDispatcherBefore()
    {

    }


    /**
     * Store response in cache
     *
     * @param array $params
     */
    public function hookActionOutputHTMLBefore(&$params)
    {
        header("Souin-Cache: true"); // to do add max age in admin panel
        $seconds_to_cache = 3600;
        $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
        header("Age: $ts");
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: public, max-age=$seconds_to_cache");
    }


}
