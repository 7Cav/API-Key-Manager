<?php

namespace Cav7\ApiKeyManager;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1(): void
    {
        $this->db()->query("
            CREATE TABLE IF NOT EXISTS `xf_cav7_api_key` (
                `key_id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
                `user_id`         INT UNSIGNED   NOT NULL,
                `key_hash`        VARBINARY(32)  NOT NULL,
                `key_prefix`      VARCHAR(12)    NOT NULL,
                `scope_read`      TINYINT(1)     NOT NULL DEFAULT 1,
                `is_active`       TINYINT(1)     NOT NULL DEFAULT 1,
                `created_date`    INT UNSIGNED   NOT NULL DEFAULT 0,
                `last_used_date`  INT UNSIGNED   NOT NULL DEFAULT 0,
                PRIMARY KEY (`key_id`),
                UNIQUE KEY `key_hash` (`key_hash`),
                UNIQUE KEY `user_id` (`user_id`),
                KEY `is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function uninstallStep1(): void
    {
        $this->db()->query("DROP TABLE IF EXISTS `xf_cav7_api_key`");
    }
}
