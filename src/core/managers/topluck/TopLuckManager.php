<?php

namespace core\managers\topluck;

use core\Main;
use core\managers\Manager;
use pocketmine\player\Player;

class TopLuckManager extends Manager
{
    private array $cache = [];

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
    }


    public function enableSession(Player $player): void {
        $this->cache[$player->getXuid()] = [
            "ore" => 0,
            "solid" => 0
        ];
    }

    public function getSessionByXuid(string $xuid): ?array {
        return $this->cache[$xuid] ?? null;
    }

    public function addOre(Player $player): void {
        $this->cache[$player->getXuid()]['ore']++;
    }

    public function addSolid(Player $player): void {
        $this->cache[$player->getXuid()]['solid']++;
    }

    public function getSession(Player $player): array {
        return $this->cache[$player->getXuid()];
    }
}