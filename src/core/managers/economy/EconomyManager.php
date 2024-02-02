<?php

namespace core\managers\economy;

use core\async\RequestAsync;
use core\events\ScoreboardReloadEvent;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use mysqli;
use pocketmine\player\Player;

class EconomyManager extends Manager
{
    public array $fastCache = [];
    public array $globalCache = [];


    public function __construct(Main $plugin)
    {
        $db = SQL::connection();

        $db->prepare("CREATE TABLE IF NOT EXISTS Economy(`xuid` VARCHAR(255) PRIMARY KEY, `money` VARCHAR(255));")->execute();

        $selectPrepare = $db->prepare("SELECT * FROM Economy");
        $selectPrepare->execute();
        $selectResult = $selectPrepare->get_result();

        while ($array = $selectResult->fetch_assoc()) {
            $money = intval($array['money']);
            if ($money < 0) $money = 0;
            $this->globalCache[$array['xuid']] = $money;
        }

        $db->close();

        parent::__construct($plugin);
    }

    public function reloadGlobalCache(): void
    {
        SQL::async(static function (RequestAsync $thread, mysqli $db) {
            $selectPrepare = $db->prepare("SELECT * FROM Economy");
            $selectPrepare->execute();
            $selectResult = $selectPrepare->get_result();
            $result = [];
            while ($array = $selectResult->fetch_array(MYSQLI_ASSOC)) {
                $money = intval($array['money']);
                if ($money < 0) $money = 0;
                $result[$array['xuid']] = $money;
            }
            $thread->setResult($result);
        }, static function (RequestAsync $thread) {
            $array = $thread->getResult();
            if (!is_null($array)) Main::getInstance()->getEconomyManager()->globalCache = $array;
        });
    }

    public function addMoneyNotName(string $xuid, int $amount): void
    {
        $this->addMoneyOffline($xuid, $amount);
    }

    public function addMoneyOffline(string $xuid, int $amount): void
    {
        if (isset($this->globalCache[$xuid])) {
            $this->globalCache[$xuid] += $amount;
        }
        if (isset($this->fastCache[$xuid])) {
            $this->fastCache[$xuid] += $amount;
        }

        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $amount) {

            $prepare = $db->prepare("SELECT * FROM Economy WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result();
            $base = 0;
            if ($result->num_rows > 0) {
                $fetch = $result->fetch_all(MYSQLI_ASSOC)[0];
                $base = $fetch['money'];
                $money = $base + $amount;
                if ($money < 0) $money = 0;
                $prepare = $db->prepare("UPDATE Economy SET money = ? WHERE xuid = ?;");
                $prepare->bind_param('is', $money, $xuid);
                $prepare->execute();
            } else {
                $money = $base + $amount;
                if ($money < 0) $money = 0;
                $insertLinePrepare = $db->prepare("INSERT INTO Economy (`xuid`, `money`) VALUES (?, ?);");
                $insertLinePrepare->bind_param('ss', $xuid, $money);
                $insertLinePrepare->execute();
            }
        });
    }

    public function getMoneyAccount(string $xuid): int
    {
        if (isset($this->fastCache[$xuid])) return $this->fastCache[$xuid];
        if (isset($this->globalCache[$xuid])) return intval($this->globalCache[$xuid]);
        return 0;
    }

    public function getAllEconomy(): int
    {
        $i = 0;
        foreach (Main::getInstance()->getDataManager()->getAllDbXuid() as $xuid) {
            $i += $this->getMoneyAccount($xuid);
        }
        return $i;
    }

    public function loadData(Player $player): void
    {
        $xuid = $player->getXuid();

        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid) {
            $xuid = $db->real_escape_string($xuid);
            $selectMoneyPrepare = $db->prepare("SELECT `money` FROM Economy WHERE `xuid` = ?;");
            $selectMoneyPrepare->bind_param('s', $xuid);
            $selectMoneyPrepare->execute();
            $selectMoneyResult = $selectMoneyPrepare->get_result();
            $money = 1000;
            if ($selectMoneyResult->num_rows > 0) $money = intval($selectMoneyResult->fetch_assoc()['money']);
            $thread->setResult(intval($money));
        }, static function (RequestAsync $thread) use ($xuid, $player) {
            $money = $thread->getResult();
            if (intval($money) < 0) $money = 0;
            Main::getInstance()->getEconomyManager()->fastCache[$xuid] = $money;
            Main::getInstance()->getEconomyManager()->globalCache[$xuid] = $money;
            if ($player->isConnected()) {
                $player->hasEconomyLoaded = true;
            }
        });
    }

    public function saveData(Player $player, bool $async = true): void
    {
        if (!isset($this->fastCache[$player->getXuid()])) return;
        $xuid = $player->getXuid();
        $money = intval($this->fastCache[$xuid]);
        if ($money < 0) $money = 0;
        unset($this->fastCache[$xuid]);
        $this->globalCache[$xuid] = $money;


        if ($async) {
            SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $money) {
                $xuid = $db->real_escape_string($xuid);
                $money = $db->real_escape_string(strval($money));
                $insertLinePrepare = $db->prepare("INSERT INTO Economy (`xuid`, `money`) VALUES (?, ?) ON DUPLICATE KEY UPDATE money = VALUES(money);");
                $insertLinePrepare->bind_param('ss', $xuid, $money);
                $insertLinePrepare->execute();
            });
        } else {
            $db = SQL::connection();
            $xuid = $db->real_escape_string($xuid);
            $money = $db->real_escape_string(strval($money));
            $insertLinePrepare = $db->prepare("INSERT INTO Economy (`xuid`, `money`) VALUES (?, ?) ON DUPLICATE KEY UPDATE money = VALUES(money);");
            $insertLinePrepare->bind_param('ss', $xuid, $money);
            $insertLinePrepare->execute();
            $db->close();
        }
    }

    public function saveAllData(): void
    {
        $db = SQL::connection();
        foreach ($this->fastCache as $xuid => $money) {
            if ($money < 0) $money = 0;
            $xuid = $db->real_escape_string($xuid);
            $money = $db->real_escape_string(strval($money));
            $insertLinePrepare = $db->prepare("INSERT INTO Economy (`xuid`, `money`) VALUES (?, ?) ON DUPLICATE KEY UPDATE money = VALUES(money);");
            $insertLinePrepare->bind_param('ss', $xuid, $money);
            $insertLinePrepare->execute();
        }
        $db->close();
    }

    public function addMoney(Player $player, int $amount, bool $isEvent = false): void
    {

        (new ScoreboardReloadEvent($player))->call();
        $this->addMoneyOffline($player->getXuid(), $amount);
    }

    public function addMoneySQL(Player $player, int $amount) {

    }

    public function existAccount(string $xuid): bool
    {
        return isset($this->globalCache[$xuid]);
    }

    public function removeMoney(Player $player, int $amount, bool $isEvent = false): void
    {
        (new ScoreboardReloadEvent($player))->call();
        if ($this->getMoney($player) - $amount < 0) {
            $this->setMoney($player->getXuid(), 0);
            return;
        }
        $this->removeMoneyOffline($player->getXuid(), $amount);
    }

    public function getMoney(Player $player): int
    {
        $xuid = $player->getXuid();
        if (isset($this->fastCache[$xuid])) return intval($this->fastCache[$xuid]);
        if (isset($this->globalCache[$xuid])) return intval($this->globalCache[$xuid]);
        return 0;
    }

    public function getMoneySQL(Player $player, callable $callback): void {
        $xuid = $player->getXuid();

        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM Economy WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $money = 0;
            $result = $prepare->get_result();
            if ($result->num_rows > 0) {
                $fetch = $result->fetch_all(MYSQLI_ASSOC)[0];
                $money = $fetch['money'];
            }
            $thread->setResult($money);
        }, static function (RequestAsync $thread) use ($player, $callback): void {
            if ($player->isConnected()) {
                $money = $thread->getResult();
                $callback($player, $money);
            }
        });
    }



    public function getMoneySQLXuid(string $xuid, callable $callback): void {
        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM Economy WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $money = 0;
            $result = $prepare->get_result();
            if ($result->num_rows > 0) {
                $fetch = $result->fetch_all(MYSQLI_ASSOC)[0];
                $money = $fetch['money'];
            }
            $thread->setResult($money);
        }, static function (RequestAsync $thread) use ($callback): void {
            $money = $thread->getResult();
            $callback($money);
        });
    }

    public function setMoney($player, int $amount): void
    {
        if ($amount < 0) $amount = 0;
        if ($player instanceof Player) {
            (new ScoreboardReloadEvent($player))->call();
            $player = $player->getXuid();
        }
        if (isset($this->fastCache[$player])) {
            $this->fastCache[$player] = $amount;
            $this->globalCache[$player] = $amount;
        } elseif (isset($this->globalCache[$player])) {
            $this->globalCache[$player] = $amount;
        }


        $xuid = $player;

        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $amount) {

            $prepare = $db->prepare("SELECT * FROM Economy WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result();
            if ($result->num_rows > 0) {
                $money = $amount;
                if ($money < 0) $money = 0;
                $prepare = $db->prepare("UPDATE Economy SET money = ? WHERE xuid = ?;");
                $prepare->bind_param('is', $money, $xuid);
                $prepare->execute();
            } else {
                $money = $amount;
                if ($money < 0) $money = 0;
                $insertLinePrepare = $db->prepare("INSERT INTO Economy (`xuid`, `money`) VALUES (?, ?);");
                $insertLinePrepare->bind_param('ss', $xuid, $money);
                $insertLinePrepare->execute();
            }
        });
    }

    public function removeMoneyOffline(string $xuid, int $amount): void
    {
        if (isset($this->globalCache[$xuid])) {
            if ($this->getMoneyAccount($xuid) - $amount < 0) {
                $this->globalCache[$xuid] = 0;
            } else $this->globalCache[$xuid] -= $amount;
        }

        if (isset($this->fastCache[$xuid])) {
            if ($this->getMoneyAccount($xuid) - $amount < 0) {
                $this->fastCache[$xuid] = 0;
            } else $this->fastCache[$xuid] -= $amount;
        }

        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $amount) {

            $prepare = $db->prepare("SELECT * FROM Economy WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result();
            $base = 0;
            if ($result->num_rows > 0) {
                $fetch = $result->fetch_all(MYSQLI_ASSOC)[0];
                $base = $fetch['money'];
                $money = $base - $amount;
                if ($money < 0) $money = 0;
                $prepare = $db->prepare("UPDATE Economy SET money = ? WHERE xuid = ?;");
                $prepare->bind_param('is', $money, $xuid);
                $prepare->execute();
            } else {
                $money = $base - $amount;
                if ($money < 0) $money = 0;
                $insertLinePrepare = $db->prepare("INSERT INTO Economy (`xuid`, `money`) VALUES (?, ?);");
                $insertLinePrepare->bind_param('ss', $xuid, $money);
                $insertLinePrepare->execute();
            }
        });
    }
}