<?php

namespace core\managers\stats;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use core\traits\UtilsTrait;
use mysqli;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class StatsManager extends Manager
{
    use UtilsTrait;

    const PATTERN = [
        'join' => 0,
        'msgSend' => 0,
        'death' => 0,
        'kill' => 0,
        'blockBreak' => 0,
        'blockPlace' => 0,
        'timeGame' => 0,
        'goldMined' => 0,
        'mobKill' => 0,
        'bossKill' => 0,
    ];


    const JOIN = 'join';
    const MSG = 'msgSend';
    const DEATH = 'death';
    const KILL = 'kill';
    const BLOCK_BREAK = 'blockBreak';
    const BLOCK_PLACE = 'blockPlace';
    const TIME = 'timeGame';
    const GOLD_MINED = 'goldMined';
    const MOB_KILL = 'mobKill';
    const BOSS_KILL = 'bossKill';


    public array $cache = [];
    public array $globalCache = [];
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = Main::getInstance();
        $db = SQL::connection();
        $db->prepare("CREATE TABLE IF NOT EXISTS `stats`(`xuid` VARCHAR(255) PRIMARY KEY, `data` TEXT);")->execute();

        $selectAllPrepare = $db->prepare("SELECT * FROM `stats`;");
        $selectAllPrepare->execute();
        $selectAllResult = $selectAllPrepare->get_result();

        while ($array = $selectAllResult->fetch_array(MYSQLI_ASSOC)) {
            $this->globalCache[$array['xuid']] = unserialize(base64_decode($array['data']));
        }

        $db->close();

        parent::__construct($plugin);
    }

    public function getStats(string $xuid): array
    {
        if (isset($this->cache[$xuid])) return $this->cache[$xuid];
        if (isset($this->globalCache[$xuid])) return $this->globalCache[$xuid];
        return self::PATTERN;
    }


    public function getAllCache(): array{
        //return the global cache uppdated if the cache is uppdated
        $return = $this->globalCache;
        foreach ($return as $xuid => $data){
            if(isset($this->cache[$xuid])){
                $return[$xuid] = $this->cache[$xuid];
            }
        }
        return $return;
    }
    public function saveAllData(): void
    {
        $db = SQL::connection();
        foreach ($this->cache as $xuid => $data) {
            if (isset($this->globalCache[$xuid])) unset($this->globalCache[$xuid]);
            $xuid = $db->real_escape_string($xuid);
            $data = $db->real_escape_string(base64_encode(serialize($data)));
            $insertPrepare = $db->prepare("INSERT INTO `stats`(`xuid`, `data`) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
            $insertPrepare->bind_param('ss', $xuid, $data);
            $insertPrepare->execute();
        }

        $db->close();
    }

    public function loadData(Player $player): void
    {
        $xuid = $player->getXuid();


        SQL::async(static function(RequestAsync $thread, mysqli $db) use ($xuid) : void {
            $selectPrepare = $db->prepare("SELECT `data` FROM `stats` WHERE `xuid` = ?;");
            $xuid = $db->real_escape_string($xuid);
            $selectPrepare->bind_param('s', $xuid);
            $selectPrepare->execute();
            $result = $selectPrepare->get_result();
            $return = false;
            if ($result->num_rows > 0) {
                $return = unserialize(base64_decode($result->fetch_assoc()['data']));
            }
            $thread->setResult($return);
        }, static function(RequestAsync $thread) use ($xuid, $player) : void {
            $result = $thread->getResult();
            if (!$result) {
                Main::getInstance()->getStatsManager()->cache[$xuid] = self::PATTERN;;
                Main::getInstance()->getStatsManager()->globalCache[$xuid] = self::PATTERN;;
            } else {
                Main::getInstance()->getStatsManager()->globalCache[$xuid] = $result;
                Main::getInstance()->getStatsManager()->cache[$xuid] = $result;
            }

            if ($player->isConnected()) {
                Main::getInstance()->getStatsManager()->addValue($player->getXuid(), self::JOIN);
                $player->hasStatsLoaded = true;
            }
        });


    }

    public function addValue(string $xuid, string $value, int $amount = 1): void
    {
        if ($value === 'join') {
            $this->getPlugin()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($xuid, $value, $amount): void {
                if (isset($this->cache[$value])) $this->cache[$xuid][$value] += $amount;
                if (isset($this->globalCache[$value])) $this->globalCache[$xuid][$value] += $amount;
            }), 200);
        } else {
            if (isset($this->cache[$xuid])) $this->cache[$xuid][$value] += $amount;
            if (isset($this->globalCache[$xuid])) $this->globalCache[$xuid][$value] += $amount;
        }
    }

    public function getPlugin(): Main
    {
        return Main::getInstance();
    }

    public function saveData(Player $player, bool $async = true): void
    {
        if (!isset($this->cache[$player->getXuid()])) return;
        if (empty($this->cache[$player->getXuid()])) return;

        $xuid = $player->getXuid();
        if (isset($this->cache[$xuid])) {
            $this->globalCache[$xuid] = $this->cache[$xuid];
            $data = base64_encode(serialize($this->cache[$xuid]));
            unset($this->cache[$xuid]);
        } else $data = base64_encode(serialize(self::PATTERN));

        if ($async) {
            SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $data): void {
                $xuid = $db->real_escape_string($xuid);
                $data = $db->real_escape_string($data);
                $insertPrepare = $db->prepare("INSERT INTO `stats`(`xuid`, `data`) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
                $insertPrepare->bind_param('ss', $xuid, $data);
                $insertPrepare->execute();
            });
        } else {
            $db = SQL::connection();
            $xuid = $db->real_escape_string($xuid);
            $data = $db->real_escape_string($data);
            $insertPrepare = $db->prepare("INSERT INTO `stats`(`xuid`, `data`) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
            $insertPrepare->bind_param('ss', $xuid, $data);
            $insertPrepare->execute();
            $db->close();
        }
    }
}