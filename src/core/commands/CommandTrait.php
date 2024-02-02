<?php

namespace core\commands;


use core\player\CustomPlayer;
use pocketmine\command\Command;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

trait CommandTrait
{
    public function createPacket(CustomPlayer|Player $player): AvailableCommandsPacket
    {
        $pk = new AvailableCommandsPacket();
        $pk->hardcodedEnums = [];
        $pk->enumConstraints = [];
        $pk->softEnums = [];


        $isRegistered = [];

        foreach (Server::getInstance()->getCommandMap()->getCommands() as $commandName => $commandData) {
            $explode = explode(":", $commandName);
            if (isset($explode[1])) {
                if ($commandData instanceof Executor) {
                    if (str_starts_with($commandName, "goldrush:")) {
                        if (!in_array($commandData->getName(), $isRegistered)) {
                            $isRegistered[] = $commandData->getName();
                            $pk->commandData[$explode[1]] = $commandData->reloadArgument($player);
                        }
                    }
                } else { //commande de base
                    $description = $commandData->getDescription();
                    $data = new CommandData(
                        $explode[1],
                        $description instanceof Translatable ? $player->getLanguage()->translate($description) : $description,
                        0,
                        0,
                        null,
                        [
                            new CommandOverload(chaining: false, parameters: [
                                CommandParameter::standard("args", AvailableCommandsPacket::ARG_TYPE_RAWTEXT, 0, true)

                            ])
                        ],
                        chainedSubCommandData: []
                    );

                    $pk->commandData[$commandName] = $data;
                }
            }
        }

        return $pk;
    }
}