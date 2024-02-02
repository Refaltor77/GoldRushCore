<?php

namespace core\commands\executors;


use core\commands\Executor;
use core\events\LogEvent;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class UnMute extends Executor
{

    public function __construct(string $name = 'unmute', string $description = "UnMute un joueur", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('unmute.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c Vous devez sélectionner un joueur !"));
            return;
        }


        $playerTarget = $this->getPlugin()->getServer()->getPlayerByPrefix($args[0]);
        if ($playerTarget instanceof Player) {
            $this->getPlugin()->getSanctionManager()->unMute($playerTarget->getXuid(), $sender);
            $sender->sendMessage(Messages::message("§aJoueur §f{$playerTarget->getName()}§a unmute !"));
            (new LogEvent($sender->getName()." a unmute ".$playerTarget->getName(), LogEvent::SANCTION_TYPE))->call();
        } else {
            $xuidTarget = $this->getPlugin()->getDataManager()->getXuidByName($args[0]);
            if (is_null($xuidTarget)) {
                $sender->sendMessage(Messages::message("§cLe joueur §f{$args[0]}§c est inexistant !"));
                return;
            }
            $this->getPlugin()->getSanctionManager()->unMute($xuidTarget, $sender);
            $sender->sendMessage("§l§c[§4Nuketown§c]§r§7 Joueur §e{$args[0]}§7 unmute !");
            (new LogEvent($sender->getName()." a unmute ".$args[0], LogEvent::SANCTION_TYPE))->call();
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Joueurs bans', true, 'Joueurs bans', $this->getPlugin()->getSanctionManager()->getAllNameMuteForArgs());
        return parent::loadOptions($player);
    }
}