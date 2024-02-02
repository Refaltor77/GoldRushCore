<?php

namespace core\managers\connexion;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use pocketmine\player\Player;

class ConnexionManager extends Manager
{
    public function __construct(Main $plugin)
    {
        $sql = SQL::connection();
        $sql->query("CREATE TABLE IF NOT EXISTS connexion_manage (xuid VARCHAR(255), ip VARCHAR(255))");


        $ip = match (Main::XUID_SERVER) {
            "XUID-GOLDRUSH-SERVER-GAME1" => "goldrushmc.fun:19132",
            "XUID-GOLDRUSH-SERVER-GAME2" => "goldrushmc.fun:19133",
            default => "goldrushmc.fun:19132"
        };

        $sql->query("DELETE FROM connexion_manage WHERE ip = '$ip';");
        $sql->close();
        parent::__construct($plugin);
    }



    public function checkOtherServerConnected(Player $player, ?callable $callbackCheckOk = null): void {
        if ($player->isOp()) return;
        $xuid = $player->getXuid();


        $ip = match (Main::XUID_SERVER) {
            "XUID-GOLDRUSH-SERVER-GAME1" => "goldrushmc.fun:19133",
            "XUID-GOLDRUSH-SERVER-GAME2" => "goldrushmc.fun:19132",
            default => "goldrushmc.fun:19132"
        };


        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid, $ip) : void  {
            $query = $db->query("SELECT * FROM connexion_manage WHERE xuid = '$xuid' AND ip = '$ip';");

            $refused = false;
            if ($query->num_rows > 0) {
                $refused = true;
            }

            $async->setResult($refused);
        }, static function(RequestAsync $async) use ($player, $callbackCheckOk): void {
            if ($player->isConnected()) {
                $refused = $async->getResult();
                if (!$refused) {
                    $callbackCheckOk($player);
                } else {

                    Main::getInstance()->getSanctionManager()->ban(
                        $player->getXuid(),
                        "Un double compte est déjà connecté sur l'un de nos serveurs de jeu. -> discord.gg/goldrush",
                        time() + 60 * 60 * 24 * 7
                    );

                }
            }
        });
    }


    public function connect(Player $player, callable $callbackConnexionAccepted): void {
        $ip = match (Main::XUID_SERVER) {
            "XUID-GOLDRUSH-SERVER-GAME1" => "goldrushmc.fun:19132",
            "XUID-GOLDRUSH-SERVER-GAME2" => "goldrushmc.fun:19133",
            default => "goldrushmc.fun:19132"
        };

        $xuid = $player->getXuid();


        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid, $ip) : void  {
            $query = $db->query("SELECT * FROM connexion_manage WHERE xuid = '$xuid';");

            $refused = false;
            if ($query->num_rows > 0) {
                $refused = true;
            }

            if (!$refused) {
                $db->query("INSERT INTO connexion_manage (xuid, ip) VALUES ('$xuid', '$ip');");
            }

            $async->setResult($refused);

        }, static function(RequestAsync $async) use ($callbackConnexionAccepted, $player, $ip): void {
            if ($player->isConnected()) {
                $refused = $async->getResult();
                if (!$refused) {
                    $callbackConnexionAccepted($player);
                } else {
                    if (!$player->isOp()) {
                        $player->kick("GOLDRUSH STAFF : Un double compte est déjà connecté sur l'un de nos serveurs de jeu.");
                    }


                    $xuid = $player->getXuid();
                    SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid, $ip): void {
                        $db->query("DELETE FROM connexion_manage WHERE xuid = '$xuid' AND ip = '$ip';");
                    });
                }
            }
        });
    }


    public function disconnect(Player $player): void {
        $xuid = $player->getXuid();

        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid) : void  {
            $db->query("DELETE FROM connexion_manage WHERE xuid = '$xuid';");
        });
    }
}