<?php

namespace core\settings\traits;

trait SettingsTrait
{
    /*
     * Created by refaltor
     * description: Trait compatible uniquement avec
     * l'interface /Settings.php
     */


    public function getServerMode(): string {
        return self::SERVER_MODE;
    }

    public function getDatabaseInformation(): array {
        return self::DB_SETTINGS;
    }
}