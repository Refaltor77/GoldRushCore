<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class TpaHere extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'tpahere', string $description = "Envoyer une demande de téléportation vers vous à un joueur", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur !"));
            return;
        }

        $player = $this->getPlugin()->getServer()->getPlayerByPrefix(strval(array_shift($args)));
        if (is_null($player)) {
            $sender->sendMessage(Messages::message("§cLe joueur n'est pas connecté !"));
            return;
        }

        if ($player->getXuid() === $sender->getXuid()) {
            $sender->sendMessage(Messages::message("§cVous ne pouvez pas vous envoyer de demande de téléporation !"));
            return;
        }

        if ($this->getPlugin()->getTpaService()->hasTpa($player)) {
            $sender->sendMessage(Messages::message("§cLe joueur a déjà une demande de téléportation !"));
            return;
        }

        $this->getPlugin()->getTpaService()->sendTpaHere($player, $sender);
        $player->sendNotification("§fLe joueur §6" . $sender->getName() . " §fvous a envoyé une demande de téléportation vers lui.");
        $sender->sendNotification("§aVous avez envoyé une demande de téléportation vers vous au joueur §f" . $player->getName());
        (new LogEvent($sender->getName()." a envoyé une demande de téléportation(TPAHERE) à ".$player->getName(), LogEvent::TP_TYPE))->call();
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $array = [];


        foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $players) {
            if ($player->getXuid() !== $players->getXuid()) {
                $array[] = strtolower($players->getName());
            }
        }


        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs connectés', $array);
        return $this->getCommandData();
    }
}