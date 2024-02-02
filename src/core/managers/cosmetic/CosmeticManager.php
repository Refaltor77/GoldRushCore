<?php

namespace core\managers\cosmetic;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\settings\Cosmetiques;
use core\sql\SQL;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\Server;

class CosmeticManager extends Manager
{

    const PATTERN = [
        'head' => [],
        'back' => [],
        'cape' => [],
        'costumes' => [],
        'other' => [],
        'pets' => []
    ];

    public array $cosmetiques = [];


    public function __construct(Main $plugin)
    {
        $db = SQL::connection();

        $db->query("CREATE TABLE IF NOT EXISTS cosmetics (xuid VARCHAR(255) PRIMARY KEY, data TEXT);");
        $db->close();

        parent::__construct($plugin);
    }


    public function hasCosmetiquesDispo(Player $player): bool {
        $head = array_keys(Cosmetiques::HEADS);
        foreach ($head as $name => $chance) {
            $name = str_replace("goldrush:", "", $name);
            if (!in_array($name, $this->cosmetiques[$player->getXuid()]["head"])) return true;
        }

        return false;
    }


    public function getCosmeticMiette(Player $player): array {
        $head = array_keys(Cosmetiques::HEADS);
        foreach ($head as $name => $chance) {
            $namee = str_replace("goldrush:", "", $name);
            if (!in_array($namee, $this->cosmetiques[$player->getXuid()]["head"])) {
                return [$name, "head"];
            }
        }
    }


    public function loadCosmet(Player $player): void {
        $xuid = $player->getXuid();

        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid): void {
            $query = $db->query("SELECT * FROM cosmetics WHERE xuid = '$xuid';");
            $result = self::PATTERN;
            if ($query->num_rows > 0) {
                $fetch = $query->fetch_assoc();
                $result = unserialize(base64_decode($fetch['data']));
            }
            $async->setResult($result);
        }, static function (RequestAsync $async) use ($player): void {
            if ($player->isConnected()) {
                Main::getInstance()->getCosmeticManager()->cosmetiques[$player->getXuid()] = $async->getResult();
            }
        });
    }


    public function hasCosmetic(Player $player, string $cosmetname, string $type): bool {
        return in_array($cosmetname, $this->cosmetiques[$player->getXuid()][$type]);
    }

    public function loadCosmetics(Player $player, callable $callback): void {
        $xuid = $player->getXuid();


        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid): void {
            $query = $db->query("SELECT * FROM cosmetics WHERE xuid = '$xuid';");
            $result = self::PATTERN;
            if ($query->num_rows > 0) {
                $fetch = $query->fetch_assoc();
                $result = unserialize(base64_decode($fetch['data']));
            }
            $async->setResult($result);
        }, static function (RequestAsync $async) use ($player, $callback): void {
            if ($player->isConnected()) {
                $callback($player, $async->getResult());
            }
        });
    }

    public function setCosmeticInPlayer(Player $player, string $name): void {
        $item = CustomiesItemFactory::getInstance()->get("goldrush:" . $name);
        $player->getOffHandInventory()->setItem(0, $item);
    }



    public function addCosmetic(string $xuid, string $cosmeticName, string $type): void {
        if (isset($this->cosmetiques[$xuid])) {
            if (!in_array($cosmeticName, $this->cosmetiques[$xuid][$type])) {
                $this->cosmetiques[$xuid][$type][] = $cosmeticName;
            }
        }

        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($xuid, $cosmeticName, $type): void {
            $query = $db->query("SELECT * FROM cosmetics WHERE xuid = '$xuid';");
            $result = self::PATTERN;
            if ($query->num_rows > 0) {
                $fetch = $query->fetch_assoc();
                $result = unserialize(base64_decode($fetch['data']));
            }
            if (!in_array($cosmeticName, $result[$type])) {
                $result[$type][] = $cosmeticName;
                $data = base64_encode(serialize($result));
                $db->query("INSERT INTO cosmetics (xuid, data) VALUES ('$xuid', '$data') ON DUPLICATE KEY UPDATE data = VALUES(data)");
            }
        });
    }


    public function removeCosmetic(string $xuid, string $cosmeticName, string $type): void {
        if (isset($this->cosmetiques[$xuid])) {
            if (in_array($cosmeticName, $this->cosmetiques[$xuid][$type])) {
                unset($this->cosmetiques[$xuid][$type][array_search($cosmeticName, $this->cosmetiques[$xuid][$type])]);
            }
        }

        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($xuid, $cosmeticName, $type): void {
            $query = $db->query("SELECT * FROM cosmetics WHERE xuid = '$xuid';");
            $result = self::PATTERN;
            if ($query->num_rows > 0) {
                $fetch = $query->fetch_assoc();
                $result = unserialize(base64_decode($fetch['data']));
            }
            if (in_array($cosmeticName, $result[$type])) {
                unset($result[$type][array_search($cosmeticName, $result[$type])]);
                $data = base64_encode(serialize($result));
                $db->query("INSERT INTO cosmetics (xuid, data) VALUES ('$xuid', '$data') ON DUPLICATE KEY UPDATE data = VALUES(data)");
            }
        });
    }
}