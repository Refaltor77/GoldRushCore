<?php

namespace core\listeners\types\commands;

use core\commands\CommandTrait;
use core\commands\Executor;
use core\Main;
use core\utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;

class CommandEvent implements Listener
{
    use CommandTrait;

    private function reloadCommandDataByNewPlayer():void{
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->isConnected()) {
                $pk = new AvailableCommandsPacket();
                foreach (Server::getInstance()->getCommandMap()->getCommands() as $commandName => $commandData) {


                    $explode = explode(":", $commandName);
                    if (isset($explode[1])) {
                        $cmd = Server::getInstance()->getCommandMap()->getCommand($commandName);
                        if ($cmd instanceof Executor) {
                            if (str_contains($commandName, 'goldrush' . ":")) {
                                $pk->commandData[$explode[1]] = $cmd->reloadArgument($player);
                            }
                        } else {
                            if ($commandData->getDescription() instanceof Translatable) {
                                $des = $player->getLanguage()->translate($commandData->getDescription());
                            } else {
                                $des = $commandData->getDescription();
                            }
                            $pk->commandData[$explode[1]] = new CommandData($commandData->getName(), $des, 0, 0, null, [], []);
                        }
                    }
                }
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        }
    }
}