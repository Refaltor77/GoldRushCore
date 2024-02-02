<?php

namespace core\managers\warp;

use core\Main;
use core\managers\Manager;
use core\traits\UtilsTrait;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class WarpManager extends Manager
{
    public array $cache;
    public Config $temp;

    use UtilsTrait;

    public function __construct(Main $plugin)
    {
        $this->temp = new Config(Main::getInstance()->getDataFolder() . 'temp/warps.json', Config::JSON);
        $this->cache = $this->temp->getAll();

        parent::__construct($plugin);
    }

    public function createWarp(Position $position, string $name): void
    {
        $this->cache[str_replace(' ', '_', strtolower($name))] = $this->positionToString($position);
        $this->save();
    }

    public function deleteWarp(string $name): void
    {
        if ($this->hasWarp($name)) {
            unset($this->cache[$name]);
        }

        $this->save();
    }

    public function hasWarp(string $nameWarp): bool
    {
        return isset($this->cache[str_replace(' ', '_', strtolower($nameWarp))]);
    }

    public function getWarp(string $name): Position
    {
        return $this->stringToPosition($this->cache[str_replace(' ', '_', strtolower($name))]);
    }

    public function getAllWarpsForArgs(): array
    {
        $arrayQueried = [];
        foreach ($this->cache as $name => $hash) {
            $arrayQueried[] = str_replace(' ', '_', strtolower($name));
        }
        return $arrayQueried;
    }

    public function save(): void
    {
        $this->temp->setAll($this->cache);
        $this->temp->save();
    }
}