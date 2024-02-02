<?php

namespace core\listeners;

use core\Main;
use pocketmine\event\Listener;

class BaseEvent implements Listener
{
    public function __construct()
    {

    }

    public function getPlugin()
    {
        return Main::getInstance();
    }
}