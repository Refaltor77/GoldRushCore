<?php

namespace core\managers\portal;

use core\Main;
use core\managers\Manager;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class PortalManager extends Manager
{

    public array $cache;
    private Config $db;

    public function __construct(Main $main)
    {
        @mkdir(Main::getInstance()->getDataFolder() . "temp");
        $file = new Config(Main::getInstance()->getDataFolder() . 'temp/portal.json', Config::JSON);
        $this->cache = $file->getAll();
        $this->db = $file;

        parent::__construct($main);
    }

    public function save(): void
    {
        $this->db->setAll($this->cache);
        $this->db->save();
    }

    public function setPositionByName(string $name, Position $pos1, Position $pos2): void
    {
        if (isset($this->cache[$name])) {
            $minimumX = intval(min($pos1->getX(), $pos2->getX()));
            $maximumX = intval(max($pos1->getX(), $pos2->getX()));
            $minimumZ = intval(min($pos1->getZ(), $pos2->getZ()));
            $maximumZ = intval(max($pos1->getZ(), $pos2->getZ()));
            $this->cache[$name]['minX'] = $minimumX;
            $this->cache[$name]['maxX'] = $maximumX;
            $this->cache[$name]['minZ'] = $minimumZ;
            $this->cache[$name]['maxZ'] = $maximumZ;
        }
    }

    public function createPortal(Position $pos1, Position $pos2, string $name, string $tp): void
    {
        $this->cache[$name] = [
            'minX' => min($pos1->getX(), $pos2->getX()),
            'maxX' => max($pos1->getX(), $pos2->getX()),
            'minZ' => min($pos2->getZ(), $pos1->getZ()),
            'maxZ' => max($pos1->getZ(), $pos2->getZ()),
            'world' => $pos1->getWorld()->getFolderName(),
            'tp' => $tp
        ];
    }

    public function getTp(Position $position): string
    {
        $x = $position->getX();
        $z = $position->getZ();
        foreach ($this->cache as $name => $value) {
            if ($x >= $value['minX'] && $x <= $value['maxX']) {
                if ($z >= $value['minZ'] && $z <= $value['maxZ']) {
                    if ($value['world'] === $position->getWorld()->getFolderName()) {
                        return $value['tp'];
                    }
                }
            }
        }
        return '404';
    }

    public function deletePortal(string $name): void
    {
        if (isset($this->cache[$name])) unset($this->cache[$name]);
    }

    public function existPortal(string $name): bool
    {
        return isset($this->cache[$name]);
    }

    public function isInPortal(Position $position): bool
    {
        $x = $position->getX();
        $z = $position->getZ();
        foreach ($this->cache as $name => $value) {
            if ($x >= $value['minX'] && $x <= $value['maxX']) {
                if ($z >= $value['minZ'] && $z <= $value['maxZ']) {
                    if ($value['world'] === $position->getWorld()->getFolderName()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getNamePortal(Position $position): string
    {
        $x = $position->getX();
        $z = $position->getZ();
        foreach ($this->cache as $name => $value) {
            if ($x >= $value['minX'] && $x <= $value['maxX']) {
                if ($z >= $value['minZ'] && $z <= $value['maxZ']) {
                    if ($value['world'] === $position->getWorld()->getFolderName()) {
                        return $name;
                    }
                }
            }
        }
        return '404';
    }
}