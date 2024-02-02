<?php

namespace core\managers\data;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use mysqli;
use pocketmine\player\Player;
use pocketmine\Server;

class DataManager extends Manager
{
    # Pourquoi tu me dirais ? tout simplement pour eviter de foreach 500 player
    # pour trouver un fucking xuid alors que avec 0.4mo de ram en plus ont evite un
    # foreach bb

    # xuid => values
    private array $cache = [];

    # name => xuid
    private array $cache2 = [];

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->prepare("CREATE TABLE IF NOT EXISTS `users_goldrush` (`xuid` VARCHAR(255) PRIMARY KEY, `pseudo` VARCHAR(255),`ipv4` VARCHAR(255))")->execute();

        $selectAllUserPrepare = $db->prepare("SELECT * FROM `users_goldrush`;");
        $selectAllUserPrepare->execute();
        $selectAllUserResult = $selectAllUserPrepare->get_result();

        while ($array = $selectAllUserResult->fetch_assoc()) {
            $this->cache[$array['xuid']] = ['ipv4' => $array['ipv4'], 'pseudo' => $array['pseudo']];
            $this->cache2[str_replace(" ", "_", strtolower($array['pseudo']))] = $array['xuid'];
        }

        $db->close();

        parent::__construct($plugin);
    }

    public function getAllDbXuid(): array
    {
        $array = [];
        foreach ($this->cache as $xuid => $values) $array[] = $xuid;
        return $array;
    }

    public function getAllNameInDatabaseForArgs(): array
    {
        return array_keys($this->cache2);
    }


    public function isConnectedXuid(string $xuid): bool
    {
        $player = $this->getPlayerXuid($xuid);
        if (!is_null($player)) return true;
        return false;
    }

    public function getIpvByXuid(string $xuid): ?string {
        $return = null;
        if (isset($this->cache[$xuid])) {
            $return = $this->cache[$xuid]['ipv4'] ?? null;
        }
        return $return;
    }

    public function getPlayerXuid(string $xuid): ?Player
    {
        $return = null;

        if (isset($this->cache[$xuid])) {
            $name = $this->cache[$xuid]['pseudo'];
            $return = $player = Server::getInstance()->getPlayerExact($name);
        }

        return $return;
    }


    public function loadDataUser(Player $player): void
    {
        $xuid = $player->getXuid();
        $ipv4 = $player->getNetworkSession()->getIp();
        $pseudo = strtolower($player->getName());
        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $ipv4, $pseudo) {
            $selectStatement = $db->prepare("SELECT * FROM `users_goldrush` WHERE `xuid` = ?;");
            $xuid = $db->real_escape_string($xuid);
            $selectStatement->bind_param('s', $xuid);
            $selectStatement->execute();
            $selectResult = $selectStatement->get_result();
            $assoc = $selectResult->fetch_assoc();
            if ($selectResult->num_rows > 0) {
                $ipv4 = $assoc['ipv4'];
                $pseudo = strtolower($assoc['pseudo']);
                $xuid = $assoc['xuid'];
            } else {
                $prepare = $db->prepare("INSERT INTO `users_goldrush` (xuid, pseudo, ipv4) VALUES (?, ?, ?);");
                $prepare->bind_param('sss', $xuid, $pseudo, $ipv4);
                $prepare->execute();
            }

            $arrayResult = [$xuid, $pseudo, $ipv4];
            $thread->setResult($arrayResult);
        }, static function (RequestAsync $thread) use ($xuid, $pseudo, $ipv4, $player) {
            $arrayResult = $thread->getResult();
            if (isset(Main::getInstance()->getDataManager()->cache[$xuid])) {
                if (Main::getInstance()->getDataManager()->cache[$xuid]['pseudo'] !== $pseudo) {
                    Main::getInstance()->getDataManager()->cache[$xuid]['pseudo'] = $arrayResult[1];
                }
                if (Main::getInstance()->getDataManager()->cache[$xuid]['ipv4'] !== $ipv4) {
                    Main::getInstance()->getDataManager()->cache[$xuid]['ipv4'] = $ipv4;
                }
            } else {
                Main::getInstance()->getDataManager()->cache[$xuid] = ['ipv4' => $arrayResult[2], 'pseudo' => $arrayResult[1]];
            }
            if ($player->isConnected()) {
                $player->hasDataLoaded = true;
            }
        });
    }


    public function getXuidByName(string $playerName): ?string
    {
        $playerName = str_replace("_", " ", $playerName);
        return $this->cache2[$playerName] ?? null;
    }

    public function getIpByXuid(string $xuid): ?string
    {
        if (!isset($this->cache[$xuid])) return null;
        return $this->cache[$xuid]['ipv4'];
    }


    public function getNameByXuid(string $xuid): null|string
    {
        if (isset($this->cache[$xuid])) return $this->cache[$xuid]['pseudo'];
        return null;
    }


    public function saveUser(Player $player, bool $async = true): void
    {
        $xuid = $player->getXuid();
        $pseudo = $player->getName();
        $ipv4 = $player->getNetworkSession()->getIp();

        if ($async) {
            SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $pseudo, $ipv4): void {
                $insertStatement = $db->prepare("INSERT INTO `users_goldrush` (`xuid`, `pseudo`, `ipv4`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE pseudo = VALUES(pseudo), ipv4 = VALUES(ipv4);");
                $insertStatement->bind_param('sss', $xuid, $pseudo, $ipv4);
                $insertStatement->execute();
            });
        } else {
            $db = SQL::connection();
            $insertStatement = $db->prepare("INSERT INTO `users_goldrush` (`xuid`, `pseudo`, `ipv4`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE pseudo = VALUES(pseudo), ipv4 = VALUES(ipv4);");
            $insertStatement->bind_param('sss', $xuid, $pseudo, $ipv4);
            $insertStatement->execute();
            $db->close();
        }
    }


    public function saveAllData(): void
    {
        $db = SQL::connection();
        foreach ($this->cache as $xuid => $values) {
            $ipv4 = $values['ipv4'];
            $pseudo = strtolower($values['pseudo']);
            $insertStatement = $db->prepare("INSERT INTO `users_goldrush` (`xuid`, `pseudo`, `ipv4`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE pseudo = VALUES(pseudo), ipv4 = VALUES(ipv4);");
            $insertStatement->bind_param('sss', $xuid, $pseudo, $ipv4);
            $insertStatement->execute();
        }

        $db->close();
    }
}