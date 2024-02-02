<?php

namespace core\managers\discord;

use core\async\Async;
use core\Main;
use core\managers\Manager;
use core\sql\Connexion;
use core\sql\SQL;
use pocketmine\color\Color;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;

class DiscordManager extends Manager
{
    public function connect(): \mysqli {
        return new \mysqli(
            "goldrushmc.fun",
            "u2_7KZpgJU41W",
            "",
            "s2_goldrush_bot");
    }

    public function processLinkCallback(Player $player, string $code, callable $callback): void {
        $xuid = $player->getXuid();
        $conn = new Connexion();
        $conn->processAsync(static function(Async $async) use ($code, $xuid) : void {
            $db = new \mysqli(
                "goldrushmc.fun",
                "u2_5X8eAE2s9X",
                "",
                "s2_goldrush_bot");

            $query = $db->query("SELECT code FROM link WHERE code = '$code';");
            $found = false;
            if ($query->num_rows > 0) {
                $found = true;
                $db->query("UPDATE link SET xuid = '$xuid', `status` = 'VERIFIED', code = null WHERE code = '$code';");
            }
            $async->setResult($found);
        }, static function (Async $async) use ($callback, $player): void {
            if ($player->isConnected()) {
                $found = $async->getResult();
                $callback($player, $found);
            }
        });
    }


    public function getParaineur(Player $player, callable $callback): void {
        $this->getDiscordId($player, function (Player $player, string $idDiscord) use ($callback) : void {
            $return = 'not-link';

            if ($idDiscord == $return) {
                $callback($player, $return);
                return;
            }

            $xuid = $player->getXuid();
            $conn = new Connexion();
            $conn->processAsync(static function(Async $async) use ($xuid, $idDiscord) : void {
                $db = new \mysqli(
                    "goldrushmc.fun",
                    "u2_5X8eAE2s9X",
                    "",
                    "s2_goldrush_bot");

                $query = $db->query("SELECT * FROM parrainage WHERE parraine = '$idDiscord';");
                $idParaineur = 'not-link';
                if ($query->num_rows > 0) {
                    $fetch = $query->fetch_assoc();
                    $idParaineur = $fetch['parraineur'];
                }
                $async->setResult($idParaineur);
            }, static function (Async $async) use ($callback, $player): void {
                if ($player->isConnected()) {
                    $idParaineur = $async->getResult();
                    $callback($player, $idParaineur);
                }
            });
        });
    }


    public function getDiscordPseudo(Player $player, callable $callback): void {
        $xuid = $player->getXuid();
        $conn = new Connexion();
        $conn->processAsync(static function(Async $async) use ($xuid) : void {
            $db = new \mysqli(
                "goldrushmc.fun",
                "u2_5X8eAE2s9X",
                "",
                "s2_goldrush_bot");

            $query = $db->query("SELECT * FROM link WHERE xuid = '$xuid';");
            $return = 'not-link';
            if ($query->num_rows > 0) {
                $return = $query->fetch_assoc()['pseudo'];
            }
            $async->setResult($return);
        }, static function (Async $async) use ($callback, $player): void {
            if ($player->isConnected()) {
                $return = $async->getResult();
                $callback($player, $return);
            }
        });
    }


    public function sendMessageDiscord(string $idDiscord, string $message, string $title = 'GoldRush', string $color = "#fde43a"): void {
        $conn = new Connexion();
        $conn->processAsync(static function(Async $async) use ($idDiscord, $message, $color, $title) : void {
            $db = new \mysqli(
                "goldrushmc.fun",
                "u2_5X8eAE2s9X",
                "",
                "s2_goldrush_bot");

            $db->query("INSERT INTO messages (id, message, title, color) VALUES ('$idDiscord', '$message', '$title', '$color');");
        });
    }



    public function getDiscordPseudoByIdDiscord(string $idDiscord, callable $callback): void {
        $conn = new Connexion();
        $conn->processAsync(static function(Async $async) use ($idDiscord) : void {
            $db = new \mysqli(
                "goldrushmc.fun",
                "u2_5X8eAE2s9X",
                "",
                "s2_goldrush_bot");

            $query = $db->query("SELECT * FROM link WHERE discord = '$idDiscord';");
            $return = 'not-link';
            if ($query->num_rows > 0) {
                $fetch = $query->fetch_assoc();
                $return = [
                    $fetch['pseudo'],
                    $fetch['xuid'],
                ];
            }
            $async->setResult($return);
        }, static function (Async $async) use ($callback): void {
            $return = $async->getResult();
            $callback($return);
        });
    }

    public function getDiscordId(Player $player, callable $callback): void {
        $xuid = $player->getXuid();
        $conn = new Connexion();
        $conn->processAsync(static function(Async $async) use ($xuid) : void {
            $db = new \mysqli(
                "goldrushmc.fun",
                "u2_5X8eAE2s9X",
                "",
                "s2_goldrush_bot");

            $query = $db->query("SELECT * FROM link WHERE xuid = '$xuid';");
            $return = 'not-link';
            if ($query->num_rows > 0) {
                $return = $query->fetch_assoc()['discord'];
            }
            $async->setResult($return);
        }, static function (Async $async) use ($callback, $player): void {
            if ($player->isConnected()) {
                $return = $async->getResult();
                $callback($player, $return);
            }
        });
    }
}