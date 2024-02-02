<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\player\CustomPlayer;
use pocketmine\command\Command;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class UnbanIp extends Executor
{
    public function __construct(string $name = "unban-ip", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("unban-ip.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if(count($args) !== 1){
            throw new InvalidCommandSyntaxException();
        }

        if(inet_pton($args[0]) !== false){
            $sender->getServer()->getIPBans()->remove($args[0]);
            $sender->getServer()->getNetwork()->unblockAddress($args[0]);
            Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_unbanip_success($args[0]));
        }else{
            $sender->sendMessage(KnownTranslationFactory::commands_unbanip_invalid());
        }

        return true;
    }

    public function getCommandData(): CommandData
    {
        $this->addOptionEnum(0,"string",false,"banned_players",$this->getAllPlayersArrayForArgs());
        return parent::getCommandData();
    }
}