<?php

namespace core\managers\xp;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\messages\Messages;
use core\sql\SQL;
use pocketmine\player\Player;

class XpManager extends Manager
{
    public array $cache = [];

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS xp_bottle_double (xuid VARCHAR(255) PRIMARY KEY, time_xp INT);");
        $db->close();
        parent::__construct($plugin);
    }


    public function loadData(Player $player): void {
        $xuid = $player->getXuid();

        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid) : void {
            $query = $db->query("SELECT * FROM xp_bottle_double WHERE xuid = '$xuid';");
            $return = [
                'time_xp' => 0
            ];

            if ($query->num_rows > 0) {
                $data = $query->fetch_assoc();
                $return['time_xp'] = $data['time_xp'];
            }

            $async->setResult($return);
        }, static function(RequestAsync $async) use ($player): void {
            if ($player->isConnected()) {
                $data = $async->getResult();
                Main::getInstance()->getXpManager()->cache[$player->getXuid()] = $data;
            }
        });
    }

    public function hasDobble(Player $player): bool {
        if (!isset($this->cache[$player->getXuid()])) return false;
        return $this->cache[$player->getXuid()]['time_xp'] > time();
    }

    public function setDobble(Player $player): void {
        $player->sendSuccessSound();
        $this->cache[$player->getXuid()]['time_xp'] = time() + 60 * 30;
        $player->sendMessage(Messages::message("§fVous êtes en §6x2§f xp pendant §630 minutes !"));
    }


    public function saveData(Player $player): void {
        $xuid = $player->getXuid();
        $cache = $this->cache[$xuid];
        unset($this->cache[$xuid]);

        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid, $cache) : void {
            $timeXp = $cache['time_xp'];
            $db->query("INSERT INTO xp_bottle_double (xuid, time_xp) VALUES ('$xuid', $timeXp) ON DUPLICATE KEY UPDATE time_xp = VALUES(time_xp);");
        });
    }
}