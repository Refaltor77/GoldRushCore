<?php

namespace core\traits;

use core\Main;
use core\player\CustomPlayer;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

trait UtilsTrait
{

    public function secondsToTime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        return "$days jours, $hours heures, $minutes minutes, $seconds secondes";
    }

    public function positionToString(Position $position): string
    {
        return $position->getX() . ':' . $position->getY() . ':' . $position->getZ() . ':' . $position->getWorld()->getFolderName();
    }

    public function positionToStringPlayer(Position $position, Player $player): string {
        return $position->getFloorX() . ':' . $position->getFloorY() . ':' . $position->getFloorZ() . ':' . $position->getWorld()->getFolderName() . ':' . $player->getXuid();
    }

    public function stringToPosition(string $posHash): ?Position {
        $explode = explode(':', $posHash);
        $x = (int)$explode[0];
        $y = (int)$explode[1];
        $z = (int)$explode[2];
        $world = Server::getInstance()->getWorldManager()->getWorldByName((string)$explode[3]);

        if (!is_null($world)) {
            return new Position($x, $y, $z, $world);
        }

        return null;
    }


    public function getAllPlayersArrayForArgs(): array
    {
        $array = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $array[str_replace(' ', '_', ($name = strtolower($player->getName())))] = $name;
        }

        foreach (Main::getInstance()->getDataManager()->getAllNameInDatabaseForArgs() as $name => $name2) {
            if (isset($array[$name])) unset($array[$name]);
        }

        $array = array_merge($array, Main::getInstance()->getDataManager()->getAllNameInDatabaseForArgs());
        foreach ($array as $name) {
            $array[array_search($name, $array)] = str_replace(' ', '_', $name);
        }
        return $array;
    }
}