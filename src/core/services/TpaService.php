<?php

namespace core\services;

use core\Main;
use core\traits\UtilsTrait;
use pocketmine\player\Player;

class TpaService
{
    use UtilsTrait;

    private array $cache = [];


    public function sendTpa(Player $target, Player $sender): void
    {
        $this->cache[$target->getXuid()] = ['type' => 'TPA', 'teleport' => $sender->getXuid(), 'time' => time() + 120];
    }

    public function getXuid(Player $target): string
    {
        return $this->cache[$target->getName()]['teleport'];
    }

    public function sendTpaHere(Player $target, Player $sender): void
    {
        $this->cache[$target->getXuid()] = ['type' => 'TPAHERE', 'teleport' => $sender->getXuid(), 'time' => time() + 120];
    }

    public function getTypeTpa(Player $target): string
    {
        return $this->cache[$target->getXuid()]['type'];
    }

    public function getSender(Player $target): ?Player
    {
        return Main::getInstance()->getDataManager()->getPlayerXuid($this->cache[$target->getXuid()]['teleport']);
    }

    public function remove(Player $target): void
    {
        if ($this->hasTpa($target)) unset($this->cache[$target->getXuid()]);
    }

    public function hasTpa(Player $target): bool
    {
        if (isset($this->cache[($xuid = $target->getXuid())])) {
            if ($this->cache[$xuid]['time'] <= time()) {
                unset($this->cache[$xuid]);
            } else return true;
        }
        return false;
    }
}