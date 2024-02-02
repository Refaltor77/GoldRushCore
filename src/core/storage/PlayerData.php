<?php

namespace core\storage;

use core\Main;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class PlayerData
{
    private Config $temp;
    private array $storage = [];

    public function __construct()
    {
        $this->temp = new Config(Main::getInstance()->getDataFolder() . 'temp/player_data.json', Config::JSON);
        $this->storage = $this->temp->getAll();
    }

    public function saveAllStorage(): void
    {
        $this->temp->setAll($this->storage);
        $this->temp->save();
    }

    public function setDataPlayer(Player $player, string $dataName, $data): void
    {
        $this->storage[$player->getXuid()][$dataName] = $data;
        $this->saveAllStorage();
    }

    public function getPlayerData(Player $player, string $dataName): mixed
    {
        return $this->storage[$player->getXuid()][$dataName] ?? [];
    }

    public function removePlayerData(Player $player, string $dataName): void
    {
        if ($this->hasPlayerData($player, $dataName)) {
            unset($this->storage[$player->getXuid()][$dataName]);
            $this->saveAllStorage();
        }
    }

    public function hasPlayerData(Player $player, string $dataName): bool
    {
        return isset($this->storage[$player->getXuid()][$dataName]);
    }


    public function setData(string $key, string $dataName, $data): void
    {
        $this->storage[$key][$dataName] = $data;
        $this->saveAllStorage();
    }

    public function getData(string $key, string $dataName): mixed
    {
        return $this->storage[$key][$dataName] ?? [];
    }

    public function removeData(string $key, string $dataName): void
    {
        if ($this->hasData($key, $dataName)) {
            unset($this->storage[$key][$dataName]);
            $this->saveAllStorage();
        }
    }

    public function hasData(string $key, string $dataName): bool
    {
        return isset($this->storage[$key][$dataName]);
    }
}