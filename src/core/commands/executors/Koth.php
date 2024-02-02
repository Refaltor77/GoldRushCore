<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\listeners\types\events\KothListeners;
use core\Main;
use core\messages\Messages;
use core\tasks\KothScheduler;
use core\tasks\Teleport;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

class Koth extends Executor
{
    const COORDS = [
        [73, 80, -260],
        [122, 76, -234],
        [103, 84, -205],
        [98, 80, -168],
        [69, 81, -160],
        [36, 81, -163],
        [11, 78, -179],
    ];


    public function __construct(string $name = 'koth', string $description = "Se rendre dans le koth", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }


    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            if (!KothScheduler::$hasKoth) {
                $sender->sendMessage(Messages::message("§cAucun koth n'est en cours pour le moment."));
                return;
            }
            $world = $sender->getWorld()->getFolderName();
            $coords = self::COORDS[array_rand(self::COORDS)];
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender, new Position($coords[0], $coords[1], $coords[2], Server::getInstance()->getWorldManager()->getDefaultWorld())), 20);
            KothListeners::$participateEvent[$sender->getXuid()] = true;
        } else {
            if ($sender->hasPermission('koth.use') || Server::getInstance()->isOp($sender->getName())) {
                switch (strtolower($args[0])) {
                    case 'start':
                        if (KothScheduler::$hasKoth) {
                            $sender->sendMessage(Messages::message("§cUn koth est déjà en cours !"));
                            return;
                        }
                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new KothScheduler(3000), 20);
                        break;
                    case 'stop':
                        if (!KothScheduler::$hasKoth) {
                            $sender->sendMessage(Messages::message("§cAucun koth n'est en cours !"));
                            return;
                        }
                        KothScheduler::$hasKoth = false;
                        break;
                }
            } else {
                if (!KothScheduler::$hasKoth) {
                    $sender->sendMessage(Messages::message("§cAucun koth n'est en cours pour le moment."));
                    return;
                }
                $world = $sender->getWorld()->getFolderName();
                $coords = self::COORDS[array_rand(self::COORDS)];
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender, new Position($coords[0], $coords[1], $coords[2], Server::getInstance()->getWorldManager()->getDefaultWorld())), 20);
            }
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}