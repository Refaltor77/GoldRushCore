<?php

namespace core\managers\area;

use core\Main;
use core\managers\Manager;
use pocketmine\utils\Config;
use pocketmine\world\Position;


class AreaManager extends Manager
{
    public array $cache;
    private Config $microDatabase;

    public function __construct(Main $plugin)
    {
        @mkdir($plugin->getDataFolder() . 'temp/');
        $path = Main::getInstance()->getDataFolder();
        $this->microDatabase = new Config($path . '/temp/db.json', Config::JSON);;
        $this->cache = $this->microDatabase->getAll();

        parent::__construct($plugin);
    }


    public function saveAllData(): void
    {
        $this->microDatabase->setAll($this->cache);
        $this->microDatabase->save();
    }

    public function setFlagsByName(string $name, array $flags): void
    {
        if (isset($this->cache[$name])) {
            $this->cache[$name]['flags'] = $flags;
        }
        $this->saveAllData();
    }

    public function setPositionByName(string $name, Position $pos1, Position $pos2): void
    {
        if (isset($this->cache[$name])) {
            $minimumX = intval(min($pos1->getX(), $pos2->getX()));
            $maximumX = intval(max($pos1->getX(), $pos2->getX()));
            $minimumZ = intval(min($pos1->getZ(), $pos2->getZ()));
            $maximumZ = intval(max($pos1->getZ(), $pos2->getZ()));
            $string = $minimumX . ':' . $maximumX . ':' . $minimumZ . ':' . $maximumZ;
            $this->cache[$name]['positions'] = $string;
        }
    }

    public function deleteAreaByName(string $name): void
    {
        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }
        $this->saveAllData();
    }

    /**
     * @param AreaManager $area
     */
    public function createArea(AreaBuild $area): void
    {
        $name = $area->getName();
        $positions = $area->getStringPosition();
        $flags = $area->getFlags();
        $this->cache[$name] = ['positions' => $positions, 'flags' => $flags, 'priority' => $area->getPriority()];
        $this->saveAllData();
    }

    public function isInArea(Position $position): bool
    {
        $x = $position->getX();
        $z = $position->getZ();
        foreach ($this->cache as $name => $value) {
            $stringExplode = explode(':', $value['positions']);
            if ($x >= $stringExplode[0] && $x <= $stringExplode[1]) {
                if ($z >= $stringExplode[2] && $z <= $stringExplode[3]) {
                    if ($stringExplode[4] === $position->getWorld()->getFolderName()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getFlagsAreaByPosition(Position $position): array
    {
        $x = $position->getX();
        $z = $position->getZ();


        $areaName = null;
        $priority = -1;

        foreach ($this->cache as $name => $value) {
            $stringExplode = explode(':', $value['positions']);
            if ($x >= $stringExplode[0] && $x <= $stringExplode[1]) {
                if ($z >= $stringExplode[2] && $z <= $stringExplode[3]) {
                    if ($stringExplode[4] === $position->getWorld()->getFolderName()) {
                        $prio = $this->getPriorityByAreaName($name);
                        if ($prio > $priority) {
                            $priority = $prio;
                            $areaName = $name;
                        }
                    }
                }
            }
        }

        if ($areaName === null) {
            return [
                'pvp' => true,
                'break' => true,
                'place' => true,
                'hunger' => true,
                'dropItem' => true,
                'chat' => true,
                'cmd' => true,
                'tnt' => true
            ];
        } else {
            return $this->getFlagsByName($areaName);
        }
    }

    public function getPriorityByAreaName(string $name): int
    {
        return $this->cache[$name]['priority'];
    }

    public function getFlagsByName(string $name): array
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name]['flags'];
        }
        return AreaBuild::createBaseFlags();
    }

    public function getAreaNamePriority(Position $position): string
    {
        $x = $position->getX();
        $z = $position->getZ();


        $areaName = null;
        $priority = -1;

        foreach ($this->cache as $name => $value) {
            $stringExplode = explode(':', $value['positions']);
            if ($x >= $stringExplode[0] && $x <= $stringExplode[1]) {
                if ($z >= $stringExplode[2] && $z <= $stringExplode[3]) {
                    if ($stringExplode[4] === $position->getWorld()->getFolderName()) {
                        $prio = $this->getPriorityByAreaName($name);
                        if ($prio > $priority) {
                            $priority = $prio;
                            $areaName = $name;
                        }
                    }
                }
            }
        }

        if ($areaName === null) {
            return '404';
        }
        return $areaName;
    }


    public function getNameAreaByPosition(Position $position): string
    {
        $x = $position->getX();
        $z = $position->getZ();
        foreach ($this->cache as $name => $value) {
            $stringExplode = explode(':', $value['positions']);
            if ($x >= $stringExplode[0] && $x <= $stringExplode[1]) {
                if ($z >= $stringExplode[2] && $z <= $stringExplode[3]) {
                    if ($stringExplode[4] === $position->getWorld()->getFolderName()) {
                        return $name;
                    }
                }
            }
        }
        return '404';
    }
}