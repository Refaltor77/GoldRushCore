<?php

namespace core\entities\classements;


use core\Main;
use core\managers\stats\StatsManager;

class TopKill extends TopEntity
{
    public int $tick = 0;

    public function onUpdate(int $currentTick): bool
    {
        if ($this->tick !== 40) {
            $this->tick++;
            return true;
        }
        $this->tick = 0;
        $top = 1;
        $array = Main::getInstance()->getStatsManager()->globalCache;

        foreach ($array as $xuid => $values) {
            $array[$xuid] = $values[StatsManager::KILL];
        }
        arsort($array);
        $nameTag = "§6---------- §f[TOP KILL] §6----------\n";
        foreach ($array as $xuid => $int) {
            $name = Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? "404";
            $nameTag .= "§6§l{$top}§r §6{$name}§f avec §e$int §fkill(s)\n";
            $top++;
            if ($top >= 11) break;
        }
        $this->setNameTag($nameTag);
        return parent::onUpdate($currentTick);
    }
}