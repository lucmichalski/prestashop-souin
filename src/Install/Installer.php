<?php

namespace PrestaShop\Module\Souin\Install;

use Db;
use Module;
use PrestaShop\Module\Souin\Form\SouinConfiguration;

class Installer
{
    public function install(Module $module): bool
    {
        $this->registerHooks($module);
        if (!$this->installDatabase()) {
            return false;
        }

        return true;
    }

    /**
     * Module's uninstallation entry point.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return $this->uninstallDatabase();
    }

    /**
     * Install the database modifications required for this module.
     *
     * @return bool
     */
    private function installDatabase(): bool
    {
        $table_name = _DB_PREFIX_.'souin';
        $engine = _MYSQL_ENGINE_;
        $creation = <<<SQL
CREATE TABLE IF NOT EXISTS `$table_name` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `configuration` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=$engine DEFAULT CHARSET=utf8;
SQL;
        $c = new SouinConfiguration();
        $c = \serialize($c);

        $insertion = <<<SQL
INSERT INTO `ps_souin` (`id`, `configuration`) VALUES (1, '$c')
SQL;

        $queries = [$creation, $insertion];

        return $this->executeQueries($queries);
    }

    /**
     * Uninstall database modifications.
     *
     * @return bool
     */
    private function uninstallDatabase(): bool
    {
        $queries = [
            'DROP TABLE IF EXISTS `'._DB_PREFIX_.'souin`',
        ];

        return $this->executeQueries($queries);
    }

    /**
     * Register hooks for the module.
     *
     * @param Module $module
     *
     * @return bool
     */
    private function registerHooks(Module $module): bool
    {
        $hooks = [
            'actionSupplierFormBuilderModifier',
            'actionAfterCreateSupplierFormHandler',
            'actionAfterUpdateSupplierFormHandler',
        ];

        return (bool) $module->registerHook($hooks);
    }

    /**
     * A helper that executes multiple database queries.
     *
     * @param array $queries
     *
     * @return bool
     */
    private function executeQueries(array $queries): bool
    {
        foreach ($queries as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }
}
