<?php

namespace core\managers\settings;

use core\async\RequestAsync;
use core\events\ScoreboardReloadEvent;
use core\Main;
use core\managers\Manager;
use core\player\CustomPlayer;
use core\sql\SQL;
use mysqli;
use pocketmine\player\Player;

class SettingsManager extends Manager
{

    public static array $cache = [];
    public static array $cacheFastInterServer = [];

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->prepare("CREATE TABLE IF NOT EXISTS `settings` (`xuid` VARCHAR(255) PRIMARY KEY , `coordinates` BOOLEAN, `private-chat` BOOLEAN, `scoreboard` BOOLEAN,`xp-jobs` BOOLEAN, `inv` BOOLEAN,`bossbar` BOOLEAN,`cps` BOOLEAN)")->execute();
        $db->close();
        parent::__construct($plugin);
    }

    public function loadData(Player|CustomPlayer $player): void
    {
        $xuid = $player->getXuid();

        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid) {
            $select = $db->prepare("SELECT * FROM `settings` WHERE `xuid` = ?;");
            $xuid = $db->real_escape_string($xuid);
            $select->bind_param('s', $xuid);
            $select->execute();
            $result = $select->get_result();
            $return = ["coordinates" => false, "private-chat" => false, "scoreboard" => true, "xp-jobs" => true, "inv" => true,"bossbar" => true,"cps" => true];
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $return = ["inv" => (bool)$row["inv"],"coordinates" => (bool)$row["coordinates"], "private-chat" => (bool)$row["private-chat"], "scoreboard" => (bool)$row["scoreboard"], "xp-jobs" => (bool)$row["xp-jobs"],"bossbar" => (bool)$row["bossbar"],"cps" => (bool)$row["cps"]];
            }
            $thread->setResult($return);
        }, static function (RequestAsync $thread) use ($xuid, $player) {
            $result = $thread->getResult();
            self::$cache[$xuid] = $result;
            if ($player->isConnected()) {
                $player->hasSettingsLoaded = true;
            }
        });
    }

    public function saveData(Player|CustomPlayer $player, bool $async = true): void
    {
        if (!isset(self::$cache[$player->getXuid()])) return;
        $xuid = $player->getXuid();
        $data = self::$cache[$xuid];
        unset(self::$cache[$xuid]);

        if ($async) {
            SQL::async(static function (RequestAsync $thred, mysqli $db) use ($xuid, $data) {
                $xuid = $db->real_escape_string($xuid);
                $coordinates = $data["coordinates"];
                $privateChat = $data["private-chat"];
                $scoreboard = $data["scoreboard"];
                $xpJobs = $data["xp-jobs"] ?? false;
                $inv = $data["inv"] ?? true;
                $bossbar = $data["bossbar"] ?? true;
                $cps = $data["cps"] ?? true;

                $query = $db->prepare("INSERT INTO `settings` (`xuid`, `coordinates`, `private-chat`, `scoreboard`, `xp-jobs`, `inv`, `bossbar`, `cps`)
                      VALUES (?, ?, ?, ?, ?, ?,?,?)
                      ON DUPLICATE KEY UPDATE
                      `coordinates` = VALUES(`coordinates`),
                      `private-chat` = VALUES(`private-chat`),
                      `scoreboard` = VALUES(`scoreboard`),
                      `xp-jobs` = VALUES(`xp-jobs`),
                      `inv` = VALUES(`inv`),
                      `bossbar` = VALUES(`bossbar`),
                        `cps` = VALUES(`cps`);
                ");

                $query->bind_param('siiiiiii', $xuid, $coordinates, $privateChat, $scoreboard, $xpJobs, $inv,$bossbar,$cps);
                $query->execute();
            });
        } else {
            $db = SQL::connection();
            $xuid = $db->real_escape_string($xuid);
            $coordinates = $data["coordinates"];
            $privateChat = $data["private-chat"];
            $scoreboard = $data["scoreboard"];
            $xpJobs = $data["xp-jobs"];
            $inv = $data["inv"] ?? true;
            $bossbar = $data["bossbar"] ?? true;
            $cps = $data["cps"] ?? true;

            $query = $db->prepare("INSERT INTO `settings` (`xuid`, `coordinates`, `private-chat`, `scoreboard`, `xp-jobs`, `inv`, `bossbar`, `cps`)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE
                      `coordinates` = VALUES(`coordinates`),
                      `private-chat` = VALUES(`private-chat`),
                      `scoreboard` = VALUES(`scoreboard`),
                      `xp-jobs` = VALUES(`xp-jobs`),
                      `inv` = VALUES(`inv`),
                      `bossbar` = VALUES(`bossbar`),
                      `cps` = VALUES(`cps`);
                ");

            $query->bind_param('siiiiiii', $xuid, $coordinates, $privateChat, $scoreboard, $xpJobs, $inv,$bossbar,$cps);
            $query->execute();
            $db->close();
        }
    }

    public function saveAllData(): void
    {
        $db = SQL::connection();
        $updateLinePrepare = $db->prepare("INSERT INTO `settings` (`xuid`, `coordinates`, `private-chat`, `scoreboard`, `xp-jobs`, `inv`, `bossbar`, `cps`)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE
                                  `coordinates` = VALUES(`coordinates`),
                                  `private-chat` = VALUES(`private-chat`),
                                  `scoreboard` = VALUES(`scoreboard`),
                                  `xp-jobs` = VALUES(`xp-jobs`),
                                  `inv` = VALUES(`inv`),
                                  `bossbar` = VALUES(`bossbar`),
                                  `cps` = VALUES(`cps`);
");

        foreach (self::$cache as $xuid => $data) {
            $xuid = $db->real_escape_string($xuid);
            $coordinates =$data["coordinates"];
            $privateChat =$data["private-chat"];
            $scoreboard = $data["scoreboard"];
            $xpJobs = $data["xp-jobs"];
            $inv = $data["inv"];
            $bossbar = $data["bossbar"];
            $cps = $data["cps"];

            $updateLinePrepare->bind_param('siiiiiii', $xuid, $coordinates, $privateChat, $scoreboard,$xpJobs, $inv,$bossbar,$cps);
            $updateLinePrepare->execute();
        }

        $updateLinePrepare->close();
        $db->close();
    }

    public function turnOnOffSetting(Player|CustomPlayer $player, string $setting): void
    {
        if (!isset(self::$cache[$player->getXuid()])) return;
        $data = self::$cache[$player->getXuid()];
        $data[$setting] = !$data[$setting];
        self::$cache[$player->getXuid()] = $data;
    }

    public function setSetting(Player|CustomPlayer $player, string $setting, bool $value): void
    {
        if (!isset(self::$cache[$player->getXuid()])) return;
        $data = self::$cache[$player->getXuid()];
        $data[$setting] = $value;
        self::$cache[$player->getXuid()] = $data;

        if ($this->getSetting($player, 'scoreboard')) {
            (new ScoreboardReloadEvent($player))->call();
        }
    }

    public function getSetting(Player|CustomPlayer $player, string $setting): bool
    {
        if (!isset(self::$cache[$player->getXuid()])) return false;
        $data = self::$cache[$player->getXuid()];
        return $data[$setting] ?? true;
    }

    public function getSettingsSql(Player|CustomPlayer $player, callable $callback): void
    {
        $xuid = $player->getXuid();

        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM `settings` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $data = ["coordinates" => false, "private-chat" => false, "scoreboard" => false, "xp-jobs" => false, "inv" => true,"bossbar" => true,"cps" => true];
            $result = $prepare->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $data = ["inv" => (bool)$row["inv"],"coordinates" => (bool)$row["coordinates"], "private-chat" => (bool)$row["private-chat"], "scoreboard" => (bool)$row["scoreboard"], "xp-jobs" => (bool)$row["xp-jobs"],"bossbar" => (bool)$row["bossbar"],"cps" => (bool)$row["cps"]];
            }
            $thread->setResult($data);
        }, static function (RequestAsync $thread) use ($player, $callback): void {
            if ($player->isConnected()) {
                $settings = $thread->getResult();
                $callback($player, $settings);
            }
        });
    }

    public function existAccount(string $xuid): bool
    {
        return isset(self::$cache[$xuid]);
    }
}