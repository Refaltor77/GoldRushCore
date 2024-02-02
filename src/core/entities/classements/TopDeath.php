<?php

namespace core\entities\classements;


use core\Main;
use core\managers\stats\StatsManager;

class TopDeath extends TopEntity
{
    public int $ticks = 0;

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
        $this->setNameTag($nameTag);
        return parent::onUpdate($currentTick);
    }
}