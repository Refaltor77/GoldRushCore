<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class TpaDeny extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'tpadeny', string $description = "Refuser une demande de téléportation", ?string $usageMessage = null, array $aliases = ['tpano', 'tpno'])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        $api = $this->getPlugin()->getTpaService();

        if (!$api->hasTpa($sender)) {
            $sender->sendMessage(Messages::message("§cVous n'avez aucune demande de téléporation !"));
            return;
        }

        $target = $api->getSender($sender);
        if (!is_null($target)) {
            $target->sendNotification("§cLe joueur §f" . $sender->getName() . " §cà refusé votre demande de téléportation.");
            $name = $target->getName();
        } else {
            $name = $this->getPlugin()->getDataManager()->getNameByXuid($api->getXuid($sender));
        }
        $api->remove($sender);
        $sender->sendMessage(Messages::message("§fVous avez refusé la demande de téléportation du joueur §6" . ($name ?? '404')));
        (new LogEvent($sender->getName()." a refusé la demande de téléportation à ".$target->getName(), LogEvent::TP_TYPE))->call();
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return $this->getCommandData();
    }
}