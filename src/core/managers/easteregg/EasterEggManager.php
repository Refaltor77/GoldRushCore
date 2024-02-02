<?php

namespace core\managers\easteregg;

use core\Main;
use core\managers\Manager;
use core\utils\Utils;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class EasterEggManager extends Manager
{
    public Config $config;


    public function __construct(Main $plugin)
    {
        $this->config = new Config(Main::getInstance()->getDataFolder() . "temp/easteregg.json", Config::JSON);
        if (!$this->config->exists('easteregg-list')) {
            $this->config->set('easteregg-list', []);
            $this->config->save();
        }
        if (!$this->config->exists('players')) {
            $this->config->set('players', []);
            $this->config->save();
        }
        parent::__construct($plugin);
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function getAll(): array
    {
        return $this->config()->getAll();
    }

    public function getAllEasterEgg(): array
    {
        return $this->config()->get("easteregg-list") ?? [];
    }

    public function addEasterEgg(Position $position): void
    {
        $config = $this->config();
        $data = $config->get("easteregg-list");
        $data[count($this->getAllEasterEgg())] = [
            "id" => count($this->getAllEasterEgg()),
            "x" => $position->getFloorX(),
            "y" => $position->getFloorY(),
            "z" => $position->getFloorZ(),
            "world" => $position->getWorld()->getFolderName()
        ];
        $config->set("easteregg-list", $data);
        $config->save();
    }

    public function removeEasterEgg(Position $position): void
    {
        $data = $this->getAllEasterEgg();
        foreach ($data as $key => $value) {
            if ((int)$value["x"] === $position->getFloorX() && (int)$value["y"] === $position->getFloorY() && (int)$value["z"] === $position->getFloorZ() && $value["world"] === $position->getWorld()->getFolderName()) {
                unset($data[$key]);
            }
        }
        $config = $this->config();
        $config->set("easteregg-list", $data);
        $config->save();

        if (count($this->getAllEasterEgg()) === 0) {
            $config->set("players", []);
        }
        $config->save();
    }

    public function isEasterEgg(Position $position): bool
    {
        $data = $this->getAllEasterEgg();
        foreach ($data as $key => $value) {
            if ((int)$value["x"] === $position->getFloorX() && (int)$value["y"] === $position->getFloorY() && (int)$value["z"] === $position->getFloorZ() && $value["world"] === $position->getWorld()->getFolderName()) {
                return true;
            }
        }
        return false;
    }

    public function getEasterEgg(Position $position): ?array
    {
        $data = $this->getAllEasterEgg();
        foreach ($data as $key => $value) {
            if ((int)$value["x"] === $position->getFloorX() && (int)$value["y"] === $position->getFloorY() && (int)$value["z"] === $position->getFloorZ() && $value["world"] === $position->getWorld()->getFolderName()) {
                return $data[$key];
            }
        }
        return null;
    }

    public function getAllPlayers(): array
    {
        return $this->config()->get("players");
    }

    public function isPlayer(string $xuid): bool
    {
        return isset($this->getAllPlayers()[$xuid]);
    }

    public function addPlayer(string $xuid): void
    {
        $config = $this->config();
        $data = $this->getAllPlayers();
        $data[$xuid] = "";
        $config->set("players", $data);
        $config->save();
    }

    public function removePlayer(string $xuid): void
    {
        $config = $this->config();
        $data = $this->getAllPlayers();
        unset($data[$xuid]);
        $config->set("players", $data);
        $config->save();
    }


    public function getPlayerCount(string $xuid): int
    {
        $data = $this->getAllPlayers();
        if (!isset($data[$xuid])) return 0;
        $list = $data[$xuid];
        $list = explode(",", $list);
        if ($list[count($list) - 1] === "") {
            unset($list[count($list) - 1]);
        }
        return count($list);
    }


    public function addPlayerEasterEgg(string $xuid, array $easterEgg): void
    {
        $players = $this->getAllPlayers();
        $list = $players[$xuid];
        $list .= $easterEgg["id"] . ",";
        $players[$xuid] = $list;
        $config = $this->config();
        $config->set("players", $players);
        $config->save();
    }

    public function playerHasEasterEgg(string $xuid, int $id): bool
    {
        $players = $this->getAllPlayers();

        if(!isset($players[$xuid])) return false;

        $list = $players[$xuid];
        $list = explode(",", $list);

        if ($list[count($list) - 1] === "") {
            unset($list[count($list) - 1]);
        }

        foreach ($list as $key => $value) {
            if ((int)$value === $id) {
                return true;
            }
        }
        return false;
    }

    public function getPlayerEasterEggs(string $xuid): array
    {
        $players = $this->getAllPlayers();

        if (!isset($players[$xuid])) return [];

        $list = explode(",", $players[$xuid]);
        unset($list[count($list) - 1]);
        return $list;
    }
}