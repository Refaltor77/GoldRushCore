<?php

namespace core\managers\enderChest;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\player\CustomPlayer;
use core\sql\SQL;
use pocketmine\player\Player;
use pocketmine\Server;

class EnderChestSlot extends Manager
{
    const PRICE = [
        0 => 1000,
        1 => 100000,
        2 => 500000,
        3 => 1000000,
        4 => 2000000
    ];


    const PATTERN = [
        0 => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false
    ];
    public array $cache;

    public function __construct(Main $main)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS  ender_chest_slot (xuid VARCHAR(255) PRIMARY KEY, `data` TEXT)");
        $db->close();

        parent::__construct($main);
    }

    public function loadUser(CustomPlayer $player): void
    {
        $xuid = $player->getXuid();

        SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM `ender_chest_slot` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result();
            $data = self::PATTERN;
            if ($result->num_rows > 0) {
                $data = unserialize(base64_decode($result->fetch_all(MYSQLI_ASSOC)[0]['data']));
            }
            $thread->setResult($data);
        }, static function (RequestAsync $thread) use ($player): void {
            if ($player->isConnected()) {
                $player->hasEnderLoaded = true;
                Main::getInstance()->getEnderChestManager()->cache[$player->getXuid()] = $thread->getResult();
            }
        });
    }

    public function saveUser(Player $player, bool $async = true): void
    {
        $xuid = $player->getXuid();
        $data = $this->cache[$player->getXuid()] ?? null;

        if ($async) {
            if (!is_null($data)) {
                SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($xuid, $data): void {
                    $data = base64_encode(serialize($data));
                    $prepare = $db->prepare("INSERT INTO `ender_chest_slot` (xuid, `data`) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
                    $prepare->bind_param('ss', $xuid, $data);
                    $prepare->execute();
                });
            }
        } else {
            if (!is_null($data)) {
                $db = SQL::connection();
                $data = base64_encode(serialize($data));
                $prepare = $db->prepare("INSERT INTO `ender_chest_slot` (xuid, `data`) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
                $prepare->bind_param('ss', $xuid, $data);
                $prepare->execute();
                $db->close();
            }
        }
    }

    public function saveAllData(bool $async = false): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $this->saveUser($player, $async);
        }
    }

    public function getSlots(Player $player): array
    {
        return $this->cache[$player->getXuid()] ?? self::PATTERN;
    }

    public function setSlot(int $slot, bool $value, Player $player): void
    {
        $this->cache[$player->getXuid()][$slot] = $value;
        $this->saveUser($player, true);
    }
}