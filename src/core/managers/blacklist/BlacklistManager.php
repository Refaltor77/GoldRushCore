<?php

namespace core\managers\blacklist;

use core\Main;
use core\managers\Manager;
use pocketmine\utils\Config;

class BlacklistManager extends Manager
{

    private Config $config;
    private array $blacklistWorld = [];

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
        $this->config = new Config($this->getPlugin()->getDataFolder() . "data/blacklist.json", Config::JSON);
    }

    private function config(): Config
    {
        return $this->config;
    }

    public function rechargeCache(): void  {
        $words = $this->getBlackList();
        $this->blacklistWorld = $words;
    }

    public function isBlackList(string $name): bool
    {
        // pour les perfs monsieur achedon
        return in_array(strtolower($name), $this->blacklistWorld);
    }

    public function getBlackList(): array
    {
        $array = [];
        foreach ($this->config()->getAll() as $word) {
            $array[] = strtolower($word);
        }
        return $array;
    }

    public function addBlackList(string $name): void
    {
        $config = $this->config();
        $blacklist = $this->getBlackList();
        $blacklist[] = $name;
        $config->setAll($blacklist);
        $config->save();
        $this->rechargeCache();
    }

    public function removeBlackList(string $name): void
    {
        $config = $this->config();
        $blacklist = $this->getBlackList();
        $new = [];
        foreach ($blacklist as $word) {
            if ($word !== $name) {
                $new[] = $word;
            }
        }
        $config->setAll($new);
        $config->save();
        $this->rechargeCache();
    }
}