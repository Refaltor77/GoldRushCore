<?php

namespace core\settings;

interface Settings
{
    const EMAIL = "contact@goldrushmc.fun";


    # server id for homes
    const SERVER_ID = "goldrush:faction:1";


    # Pour mettre le serveur sur production
    # remplacer le mode sur "PROD"
    const SERVER_MODE = "DEV";


    # Information de connexion
    # à la base de données
    const DB_SETTINGS = [
        'hostname' => '127.0.0.1',
        'username' => 'root',
        'database' => 'grv2',
        'password' => '',
        'port' => '3306'
    ];

    const DB_SETTINGS_GRAFANA = [
        'hostname' => '127.0.0.1',
        'username' => 'root',
        'database' => 'grv2',
        'password' => '',
        'port' => '3306'
    ];
}