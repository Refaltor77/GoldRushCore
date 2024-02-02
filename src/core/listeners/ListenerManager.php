<?php

namespace core\listeners;

use core\Main;
use core\utils\Utils;
use pocketmine\Server;

class ListenerManager
{
    public function init()
    {
        Utils::callDirectory("listeners" . DIRECTORY_SEPARATOR . "types", function (string $namespace): void {
            Server::getInstance()->getPluginManager()->registerEvents(new $namespace(), Main::getInstance());
        });
    }
}