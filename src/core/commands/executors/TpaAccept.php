<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\messages\Messages;
use core\tasks\Teleport;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class TpaAccept extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'tpaccept', string $description = "Accepter une demande de téléportation", ?string $usageMessage = null, array $aliases = ['tpayes', 'tpyes'])
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

        $type = $api->getTypeTpa($sender);

        switch ($type) {
            case 'TPA':
                $target = $api->getSender($sender);
                if (is_null($target)) {
                    $sender->sendMessage(Messages::message("§cLe joueur s'est déconnecté."));
                    return;
                }
                $target->sendNotification("§aLe joueur §f" . $sender->getName() . "§a à accepté votre demande de téléportation.");
                $sender->sendNotification("§aVous avez accepté la demande de téléportation.");

                $position = $sender->getPosition();
                $this->getPlugin()->getScheduler()->scheduleRepeatingTask(new Teleport($target, $position), 20);
                $api->remove($sender);
                break;
            case 'TPAHERE':
                $target = $api->getSender($sender);
                if (is_null($target)) {
                    $sender->sendMessage(Messages::message("§cLe joueur s'est déconnecté."));
                    return;
                }
                $target->sendNotification("§aLe joueur §f" . $sender->getName() . "§a à accepté votre demande de téléportation.");
                $sender->sendNotification("§aVous avez accepté la demande de téléportation.");
                $position = $target->getPosition();
                $this->getPlugin()->getScheduler()->scheduleRepeatingTask(new Teleport($sender, $position), 20);
                $api->remove($sender);
                break;
        }
        (new LogEvent($sender->getName()." a accepté la demande de téléportation à ".$target->getName(), LogEvent::TP_TYPE))->call();
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}