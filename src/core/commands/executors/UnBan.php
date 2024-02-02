<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class UnBan extends Executor
{
    public function __construct(string $name = 'unban', string $description = "Unban un joueur", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('unban.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c Vous devez sélectionner un joueur !"));
            return;
        }

        $xuidTarget = $this->getPlugin()->getDataManager()->getXuidByName($args[0]);
        if (is_null($xuidTarget)) {
            $sender->sendMessage(Messages::message("§cLe joueur §f{$args[0]}§c est inexistant !"));
            return;
        }

        $this->getPlugin()->getSanctionManager()->unBan($xuidTarget, $sender);
        (new LogEvent($sender->getName()." a unban ".$args[0], LogEvent::SANCTION_TYPE))->call();
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Joueurs bans', true, 'Joueurs bans', $this->getPlugin()->getSanctionManager()->getAllNameBanForArgs());
        return parent::loadOptions($player);
    }
}