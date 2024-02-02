<?php

namespace core\managers\wiki;

use core\Main;
use core\managers\Manager;
use pocketmine\utils\Config;

class WikiManager extends Manager
{

    const RARITY_COMMON = "common";
    const RARITY_RARE = "rare";
    const RARITY_EPIC = "epic";
    const RARITY_LEGENDARY = "legendary";

    const TYPE_BLOCK = "block";
    const TYPE_WEAPON = "arme";
    const TYPE_ARMOR = "armure";
    const TYPE_TOOL = "outils";
    const TYPE_FOOD = "nourriture";
    const TYPE_OTHER = "autre";
    const TYPE_MINERAL = "minerais";

    public function config(): Config
    {
        return new Config(Main::getInstance()->getDataFolder() . "wiki.json", Config::JSON);
    }

    public function getAll(): array
    {
        return $this->config()->getAll();
    }

    public function getWiki(string $name): array
    {
        return $this->getAll()[$name];
    }

    public function isWiki(string $name): bool
    {
        return isset($this->getAll()[$name]);
    }

    public function addWiki(string $name, string $description, string $rarity, string $type): void
    {
        $config = $this->config();
        $config->set($name, [
            "name" => $name,
            "description" => $description,
            "rarity" => $rarity,
            "type" => $type
        ]);
        $config->save();
    }

    public function removeWiki(string $name): void
    {
        $config = $this->config();
        $all = $config->getAll();
        unset($all[$name]);
        $config->setAll($all);
        $config->save();
    }

    public function updateWiki(string $name, string $description, string $rarity, string $type): void
    {
        $this->addWiki($name, $description, $rarity, $type);
    }

    public function getWikiByName(string $name): ?array
    {
        foreach ($this->getAll() as $wiki) {
            if ($wiki["name"] === $name) {
                return $wiki;
            }
        }
        return null;
    }

    public function getWikiByRarity(string $rarity): array
    {
        $wikis = [];
        foreach ($this->getAll() as $wiki) {
            if ($wiki["rarity"] === $rarity) {
                $wikis[] = $wiki;
            }
        }
        return $wikis;
    }

    public function getWikiByType(string $type): array
    {
        $wikis = [];
        foreach ($this->getAll() as $wiki) {
            if ($wiki["type"] === $type) {
                $wikis[] = $wiki;
            }
        }
        return $wikis;
    }

    public function getAllTypes(): array
    {
        return [
            ucfirst(self::TYPE_BLOCK),
            ucfirst(self::TYPE_WEAPON),
            ucfirst(self::TYPE_ARMOR),
            ucfirst(self::TYPE_TOOL),
            ucfirst(self::TYPE_FOOD),
            ucfirst(self::TYPE_OTHER),
            ucfirst(self::TYPE_MINERAL)
        ];
    }

    public function getAllRarity(): array
    {
        return [
            ucfirst(self::RARITY_COMMON),
            ucfirst(self::RARITY_RARE),
            ucfirst(self::RARITY_EPIC),
            ucfirst(self::RARITY_LEGENDARY)
        ];
    }
}