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

class JobsStorageCache
{
    private array $cache = [];

    public function __construct()
    {
        SQL::query("CREATE TABLE IF NOT EXISTS `jobs_storage_cache` (xuid VARCHAR(255) PRIMARY KEY, `data` LONGTEXT);");
    }

    public function setInv(Player $player, array $items) {
        $this->cache[$player->getXuid()] = $items;
        $this->saveUserCache($player, true);
    }

    public function loadUserCache(CustomPlayer $player): void
    {
        $xuid = $player->getXuid();

        SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM `jobs_storage_cache` WHERE `xuid` = ?;");
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
                $player->hasJobsStorageLoaded = true;
                $data = $thread->getResult();
                $arrayQueried = [];
                foreach ($data as $itemSerialized) {
                    $arrayQueried[] = Item::nbtDeserialize(unserialize(base64_decode($itemSerialized)));
                }
                Main::getInstance()->jobsStorage->cache[$player->getXuid()] = $arrayQueried;
            }
        });
    }

    public function saveUserCache(CustomPlayer|Player $player, bool $async = true, ?\mysqli $db = null, bool $clear = false): void
    {
        if (!isset($this->cache[$player->getXuid()])) return;
        $xuid = $player->getXuid();
        $data = $this->cache[$xuid];
        $arrayQueried = [];
        foreach ($data as $item) {
            $itemSerialized = Utils::serilizeItem($item);
            $arrayQueried[] = $itemSerialized;
        }

        if ($clear) {
            unset($this->cache[$player->getXuid()]);
        }

        if ($async) {
            SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($xuid, $arrayQueried): void {
                $arrayQueried = base64_encode(serialize($arrayQueried));
                $prepare = $db->prepare("INSERT INTO `jobs_storage_cache` (xuid, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
                $prepare->bind_param('ss', $xuid, $arrayQueried);
                $prepare->execute();
            });
        } else {
            $mysqli = SQL::connection();
            $arrayQueried = base64_encode(serialize($arrayQueried));
            $prepare = $mysqli->prepare("INSERT INTO `jobs_storage_cache` (xuid, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
            $prepare->bind_param('ss', $xuid, $arrayQueried);
            $prepare->execute();
            $mysqli->close();
        }
    }






    public function getInventoryPlayerJobs(CustomPlayer|Player $player): array {
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