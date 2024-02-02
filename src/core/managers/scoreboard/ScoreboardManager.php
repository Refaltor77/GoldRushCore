<?php

namespace core\managers\scoreboard;

use core\api\scoreboard\ScoreboardAPI;
use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use mysqli;
use pocketmine\player\Player;
use pocketmine\Server;

class ScoreboardManager extends Manager
{

    const PATTERN = [
        'money' => true,
        'cps' => false,
        'faction' => true,
        'online_player' => true,
        'gold' => false,
    ];


    public array $cache = [];


    private ScoreboardAPI $scoreboardApi;

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS scoreboard (xuid VARCHAR(255) PRIMARY KEY, data TEXT);");
        $db->close();

        $this->scoreboardApi = new ScoreboardAPI();
        parent::__construct($plugin);
    }

    public function getData(Player $player): array {
        return $this->cache[$player->getXuid()] ?? self::PATTERN;
    }

    public function setData(Player $player, array $data): array {
        return $this->cache[$player->getXuid()] = $data;
    }


    public function loadData(Player $player): void {
        $xuid = $player->getXuid();

        SQL::async(static function(RequestAsync $async, mysqli $db) use ($xuid) : void {
            $query = $db->query("SELECT * FROM scoreboard WHERE xuid = '$xuid';");
            $result = $query;
            $data = self::PATTERN;
            if ($result->num_rows > 0) {
                $data = $result->fetch_all(MYSQLI_ASSOC)[0]['data'];
                $data = unserialize(base64_decode($data));
            }
            $async->setResult($data);
        }, static function(RequestAsync $async) use ($player) : void {
            $data = $async->getResult();
            if ($player->isConnected()) {
                Main::getInstance()->getScoreboardManager()->cache[$player->getXuid()] = $data;
                $player->hasSoreboardLoaded = true;
            }
        });
    }



    public function saveData(Player $player, bool $async = true): void
    {
        $xuid = $player->getXuid();
        $data = base64_encode(serialize($this->cache[$xuid] ?? self::PATTERN));


        if ($async) {
            SQL::async(static function (RequestAsync $async, mysqli $db) use ($data, $xuid): void {
                $prepare = $db->prepare("INSERT INTO scoreboard (xuid, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
                $prepare->bind_param('ss', $xuid, $data);
                $prepare->execute();
            });
        } else {
            $db = SQL::connection();
            $prepare = $db->prepare("INSERT INTO scoreboard (xuid, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
            $prepare->bind_param('ss', $xuid, $data);
            $prepare->execute();
            $db->close();
        }
    }



    public function saveAllData(): void {
        $db = SQL::connection();
        $prepare = $db->prepare("INSERT INTO scoreboard (xuid, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)");
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $xuid = $player->getXuid();
            $data = base64_encode(serialize($this->cache[$xuid] ?? self::PATTERN));
            $prepare->bind_param('ss', $xuid, $data);
            $prepare->execute();
        }
        $prepare->close();
        $db->close();
    }



    public function getScoreboardApi(): ScoreboardAPI {
        return $this->scoreboardApi;
    }
}