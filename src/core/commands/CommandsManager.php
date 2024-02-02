<?php

namespace core\commands;
use core\utils\Utils;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;

class CommandsManager
{
    public function init()
    {
        PermissionManager::getInstance()->addPermission(new Permission("bourse_spawn.use"));
        Utils::callDirectory("commands" . DIRECTORY_SEPARATOR . "executors", function (string $namespace): void{
            Server::getInstance()->getCommandMap()->register('goldrush', new $namespace());
        });
    }
}