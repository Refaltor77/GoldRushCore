<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use core\tasks\Teleport;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;
use pocketmine\world\Position;

class Farmzone extends Executor
{
    public function __construct(string $name = 'farmzone', string $description = "Se rendre dans la farmzone", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (in_array(Main::getInstance()->getRankManager()->getRankPriority($sender->getXuid()), [
            'BANDIT',
            'BRAQUEUR',
            'COWBOY',
            'MARSHALL',
            'SHERIF',
        ])) {
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender,
            new Position(-21, 66, 122, Server::getInstance()->getWorldManager()->getWorldByName('farmzone'))), 20);
        } else {
            switch (mt_rand(0, 2)) {
                case 0:
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender,
                        new Position(120, 68, 116, Server::getInstance()->getWorldManager()->getWorldByName('farmzone'))), 20);
                    break;
                case 1:
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender,
                        new Position(167, 68, -16, Server::getInstance()->getWorldManager()->getWorldByName('farmzone'))), 20);
                    break;
                case 2:
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender,
                        new Position(-7, 68, -40, Server::getInstance()->getWorldManager()->getWorldByName('farmzone'))), 20);
                    break;
            }
        }
    }
}