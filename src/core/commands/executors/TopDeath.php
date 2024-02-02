<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\managers\stats\StatsManager;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class TopDeath extends Executor
{
    public function __construct(string $name = "topdeath", string $description = "Voir le classement des joueurs lesplus morts", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $top = 1;
        $array = Main::getInstance()->getStatsManager()->getAllCache();

        foreach ($array as $xuid => $values) {
            $array[$xuid] = $values[StatsManager::DEATH];
        }
        arsort($array);
        $nameTag = "§6---------- §f[TOP DEATH] §6----------\n";
        foreach ($array as $xuid => $int) {
            $name = Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? "404";
            $nameTag .= "§6§l{$top}§r §6{$name}§f avec §e$int §fmort(s)\n";
            $top++;
            if ($top >= 11) break;
        }
        $sender->sendMessage($nameTag);
    }
}