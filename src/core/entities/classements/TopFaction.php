<?php

namespace core\entities\classements;


use core\Main;

class TopFaction extends TopEntity
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
        $array = Main::getInstance()->getFactionManager()->fastCacheFaction;

        foreach ($array as $factionName => $values) {
            $array[$factionName] = $values['power'];
        }
        arsort($array);
        $nameTag = "§6---------- §f[TOP FACTION] §6----------\n";
        foreach ($array as $factionName => $int) {
            $nameTag .= "§6§l{$top}§r §6{$factionName}§f avec §e$int §fpower(s)\n";
            $top++;
            if ($top >= 11) break;
        }
        $this->setNameTag($nameTag);
        return parent::onUpdate($currentTick);
    }
}