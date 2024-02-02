<?php

namespace core\storage;

use core\async\ComplexThread;
use core\managers\Inventory;
use core\player\CustomPlayer;
use core\sql\SQL;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\Server;

class FakeInventory
{
    private array $cache = [];

    public function __construct()
    {
        SQL::query("CREATE TABLE IF NOT EXISTS `storage_fake_inv` (xuid VARCHAR(255), `data` TEXT);");
    }

    public function loadUserCache(CustomPlayer $player): void
    {
        $xuid = $player->getXuid();

        SQL::async(function (ComplexThread $thread, \mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM `storage_fake_inv` WHERE `xuid` = ?;");
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
        }, function (ComplexThread $thread) use ($player): void {
            if ($player->isConnected()) {
                $data = $thread->getResult();
                $arrayQueried = [];
                foreach ($data as $itemSerialized) {
                    $arrayQueried[] = Item::nbtDeserialize(unserialize(base64_decode($itemSerialized)));
                }
                $this->cache[$player->getXuid()] = $arrayQueried;
            }
        });
    }

    public function saveUserCache(CustomPlayer|Player $player, bool $async = true, ?\mysqli $db = null): void
    {
        if (!isset($this->cache[$player->getXuid()])) return;
        $xuid = $player->getXuid();
        $data = $this->cache[$xuid];
        $arrayQueried = [];
        foreach ($data as $item) {
            $itemSerialized = Inventory::serilizeItemStatic($item);
            $arrayQueried[] = $itemSerialized;
        }

        if ($async) {
            SQL::async(function (ComplexThread $thread, \mysqli $db) use ($xuid, $arrayQueried): void {
                $arrayQueried = base64_encode(serialize($arrayQueried));
                $prepare = $db->prepare("DELETE FROM `storage_fake_inv` WHERE `xuid` = ?;");
                $prepare->bind_param('s', $xuid);
                $prepare->execute();
                $prepare = $db->prepare("INSERT INTO `storage_fake_inv` (xuid, `data`) VALUES (?, ?);");
                $prepare->bind_param('ss', $xuid, $arrayQueried);
                $prepare->execute();
            });
        } else {
            if ($db !== null) {
                $mysqli = $db;
            } else $mysqli = SQL::connection();
            $arrayQueried = base64_encode(serialize($arrayQueried));
            $prepare = $mysqli->prepare("DELETE FROM `storage_fake_inv` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $prepare = $mysqli->prepare("INSERT INTO `storage_fake_inv` (xuid, `data`) VALUES (?, ?);");
            $prepare->bind_param('ss', $xuid, $arrayQueried);
            $prepare->execute();
            if ($db === null) $mysqli->close();
        }
    }


    public function getInv(CustomPlayer $player): array {
        return $this->cache[$player->getXuid()] ?? [];
    }

    public function setInv(CustomPlayer|Player $player, array $items) : void {
        $this->cache[$player->getXuid()] = $items;
    }

    public function addItemInStorage(CustomPlayer|Player $player, Item $item): void
    {
        $this->cache[$player->getXuid()][] = $item;
    }


    public function saveAllData(): void {
        // OPTI sauvegarde dans la main
    }
}