<?php

namespace core\managers\kits;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use pocketmine\player\Player;

class KitManager extends Manager
{
    const TYPE_PVP = "Pvp";
    const TYPE_FARM = "Farm";

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS kits (
            xuid VARCHAR(255) PRIMARY KEY,
            player INT,
            bandit INT,
            braqueur INT,
            cowboy INT,
            marshall INT,
            sherif INT
    )");
        parent::__construct($plugin);
    }


    public function checkupUser(Player $player): void {
        $xuid = $player->getXuid();

        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid) : void {
            $select = $db->query("SELECT * FROM kits WHERE xuid = '$xuid';");
            if ($select->num_rows <= 0) {
                $time = time();
                $db->query("INSERT INTO kits (
                   xuid,
                   player,
                   bandit,
                   braqueur,
                   cowboy,
                   marshall,
                   sherif
                  ) VALUES ('$xuid', $time, $time, $time, $time, $time, $time);");
            }
        });
    }


    public function getCooldownKit(Player $player, string $rank, callable $callback): void {
        $xuid = $player->getXuid();

        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid, $rank): void {
            $rankKit = strtolower($rank);
            $select = $db->query("SELECT $rankKit FROM kits WHERE xuid = '$xuid';")->fetch_all(MYSQLI_ASSOC);
            $cooldown = time();
            foreach ($select as $row => $value) {
                if (isset($value[$rankKit])) {
                    $cooldown = $value[$rankKit];
                }
            }
            $async->setResult($cooldown);
        }, static function (RequestAsync $async) use ($callback, $player): void {
            if ($player->isConnected()) {
                $callback($player, $async->getResult());
            }
        });
    }


    public function setCooldownKit(Player $player, string $rank, ?callable $callback = null): void {
        $xuid = $player->getXuid();

        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid, $rank) : void {
            $rankKit = $rank;
            $cooldown = time() + 60 * 60 * 12;
            $db->query("UPDATE kits SET $rankKit = $cooldown WHERE xuid = '$xuid';");
        }, static function(RequestAsync $async) use ($callback): void {
            if ($callback !== null) {
                $callback();
            }
        });
    }
}