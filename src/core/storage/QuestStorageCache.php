<?php

namespace core\storage;

use core\async\RequestAsync;
use core\Main;
use core\player\CustomPlayer;
use core\sql\SQL;
use core\utils\Utils;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\Server;

class QuestStorageCache
{
    private array $cache = [];

    public function __construct()
    {
        SQL::query("CREATE TABLE IF NOT EXISTS `quest_storage_cache` (xuid VARCHAR(255), `data` LONGTEXT);");
    }

    public function setInv(Player $player, array $items) {
        $this->cache[$player->getXuid()] = $items;
        $this->saveUserCache($player, true);
    }

    public function loadUserCache(CustomPlayer $player): void
    {
        $xuid = $player->getXuid();

        SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM `quest_storage_cache` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result();
            if ($result->num_rows > 0) {
                $fetch = $result->fetch_all(MYSQLI_ASSOC);
                $data = unserialize(base64_decode($fetch[0]['data']));
            } else {
                $data = [];
            }
            $thread->setResult($data);
        }, static function (RequestAsync $thread) use ($player): void {
            if ($player->isConnected()) {
                $player->hasQuestStorageLoaded = true;
                $data = $thread->getResult();
                $arrayQueried = [];
                foreach ($data as $itemSerialized) {
                    $arrayQueried[] = Item::nbtDeserialize(unserialize(base64_decode($itemSerialized)));
                }
                Main::getInstance()->questStorage->cache[$player->getXuid()] = $arrayQueried;
            }
        });
    }

    public function saveUserCache(CustomPlayer|Player $player, bool $async = true, ?\mysqli $db = null): void
    {
        $xuid = $player->getXuid();
        $data = $this->cache[$xuid] ?? [];
        $arrayQueried = [];
        foreach ($data as $item) {
            $itemSerialized = Utils::serilizeItem($item);
            $arrayQueried[] = $itemSerialized;
        }

        if ($async) {
            SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($xuid, $arrayQueried): void {


                $prepare = $db->prepare("SELECT * FROM quest_storage_cache WHERE xuid = ?;");
                $prepare->bind_param('s', $xuid);
                $prepare->execute();
                $prepare->store_result();
                if ($prepare->num_rows > 0) {
                    $arrayQueried = base64_encode(serialize($arrayQueried));
                    $prepare = $db->prepare("UPDATE `quest_storage_cache` SET `data`= ? WHERE xuid = ?;");
                    $prepare->bind_param('ss', $arrayQueried, $xuid);
                    $prepare->execute();
                    $prepare->store_result();
                } else {
                    $arrayQueried = base64_encode(serialize($arrayQueried));
                    $prepare = $db->prepare("INSERT INTO `quest_storage_cache` (xuid, `data`) VALUES (?, ?);");
                    $prepare->bind_param('ss', $xuid, $arrayQueried);
                    $prepare->execute();
                    $prepare->store_result();
                }
            });
        } else {
            if ($db !== null) {
                $mysqli = $db;
            } else $mysqli = SQL::connection();
            $prepare = $mysqli->prepare("SELECT * FROM quest_storage_cache WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $prepare->store_result();
            if ($prepare->num_rows > 0) {
                $arrayQueried = base64_encode(serialize($arrayQueried));
                $prepare = $mysqli->prepare("UPDATE `quest_storage_cache` SET `data`= ? WHERE xuid = ?;");
                $prepare->bind_param('ss', $arrayQueried, $xuid);
                $prepare->execute();

                $prepare->store_result();

            } else {
                $arrayQueried = base64_encode(serialize($arrayQueried));
                $prepare = $mysqli->prepare("INSERT INTO `quest_storage_cache` (xuid, `data`) VALUES (?, ?);");
                $prepare->bind_param('ss', $xuid, $arrayQueried);
                $prepare->execute();

                $prepare->store_result();

            }
            if ($db === null) $mysqli->close();
        }
    }






    public function getInventoryPlayer(CustomPlayer|Player $player): array {
        $a = $this->cache[$player->getXuid()] ?? [];
        return $a;
    }

    public function addItemInStorage(CustomPlayer|Player $player, Item $item): void
    {
        $this->cache[$player->getXuid()][] = $item;
    }


    public function saveAllData(): void {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $this->saveUserCache($player, false);
        }
    }
}