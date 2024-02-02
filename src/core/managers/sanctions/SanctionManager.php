<?php

namespace core\managers\sanctions;

use core\api\timings\TimingsSystem;
use core\async\RequestAsync;
use core\cooldown\BasicCooldown;
use core\Main;
use core\managers\Manager;
use core\messages\Prefix;
use core\player\CustomPlayer;
use core\sql\SQL;
use core\traits\UtilsTrait;
use mysqli;
use pocketmine\command\defaults\BanIpCommand;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class SanctionManager extends Manager
{
    use UtilsTrait;

    public array $cache = [
        'ban' => [],
        'mute' => [],
        'warn' => []
    ];
    public array $queue = [
        'ban' => [],
        'mute' => [],
        'warn' => []
    ];
    public array $staffMode = [];
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = Main::getInstance();
        $db = SQL::connection();
        $db->prepare("CREATE TABLE IF NOT EXISTS `ban` (`xuid` VARCHAR(255), `reason` VARCHAR(255), `timeReste` BIGINT, `dateT` INT, ipv4 VARCHAR(255))")->execute();
        $db->prepare("CREATE TABLE IF NOT EXISTS `mute` (`xuid` VARCHAR(255), `reason` VARCHAR(255), `timeReste` BIGINT, `dateT` INT)")->execute();
        $db->prepare("CREATE TABLE IF NOT EXISTS `warn` (`xuid` VARCHAR(255), `warn` TEXT)")->execute();

        $selectBan = $db->prepare("SELECT * FROM `ban`;");
        $selectBan->execute();
        $resultBan = $selectBan->get_result();

        $selectMute = $db->prepare("SELECT * FROM `mute`;");
        $selectMute->execute();
        $resultMute = $selectMute->get_result();

        $selectWarn = $db->prepare("SELECT * FROM `warn`;");
        $selectWarn->execute();
        $resultWarn = $selectWarn->get_result();

        while ($array = $resultBan->fetch_array(1)) {
            $this->cache['ban'][$array['xuid']] = ['reason' => $array['reason'], 'timeReste' => $array['timeReste'], 'dateT' => $array['dateT'], 'ipv4' => $array['ipv4']];
        }
        while ($array = $resultMute->fetch_array(1)) {
            $this->cache['mute'][$array['xuid']] = ['reason' => $array['reason'], 'timeReste' => $array['timeReste'], 'dateT' => $array['dateT']];
        }
        while ($array = $resultWarn->fetch_array(1)) {
            $this->cache['warn'][$array['xuid']] = unserialize(base64_decode($array['warn']));
        }

        $db->close();

        TimingsSystem::schedule(function (TimingsSystem $timingsSystem, int $second): void {
            $this->rechargeCache();
        }, 20 * 10);

        parent::__construct($plugin);
    }

    public function rechargeCache(): void
    {
        SQL::async(static function(RequestAsync $async, mysqli $db): void {
            $selectBan = $db->prepare("SELECT * FROM `ban`;");
            $selectBan->execute();
            $resultBan = $selectBan->get_result();

            $selectMute = $db->prepare("SELECT * FROM `mute`;");
            $selectMute->execute();
            $resultMute = $selectMute->get_result();

            $selectWarn = $db->prepare("SELECT * FROM `warn`;");
            $selectWarn->execute();
            $resultWarn = $selectWarn->get_result();

            $cache = [];

            while ($array = $resultBan->fetch_array(1)) {
                $cache['ban'][$array['xuid']] = ['reason' => $array['reason'], 'timeReste' => $array['timeReste'], 'dateT' => $array['dateT'], 'ipv4' => $array['ipv4']];
            }
            while ($array = $resultMute->fetch_array(1)) {
                $cache['mute'][$array['xuid']] = ['reason' => $array['reason'], 'timeReste' => $array['timeReste'], 'dateT' => $array['dateT']];
            }
            while ($array = $resultWarn->fetch_array(1)) {
                $cache['warn'][$array['xuid']] = unserialize(base64_decode($array['warn']));
            }
            $async->setResult($cache);
        }, static function(RequestAsync $async): void {
            $cache = $async->getResult();
            Main::getInstance()->getSanctionManager()->cache = (array)$cache;
        });
    }

    public function addWarn(string $xuid, string $reason): void
    {
        $this->queue['warn'][$xuid][date('d/m/Y H:i:s', time())] = $reason;
        $this->cache['warn'][$xuid][date('d/m/Y H:i:s', time())] = $reason;
        $this->saveAllData();
    }

    public function saveAllData(bool $isClosed = false): void
    {
        $db = SQL::connection();
        if (!isset($this->cache['ban'])) return;
        foreach ($this->cache['ban'] as $xuid => $values) {
            $xuid = $db->real_escape_string($xuid);
            $reason = $db->real_escape_string($values['reason']);
            $timeReste = intval($values['timeReste']);
            $dateT = intval($values['dateT']);
            $ipv4 = intval($values['ipv4']);

            $deletePrepare = $db->prepare("DELETE FROM `ban` WHERE `xuid` = ?;");
            $deletePrepare->bind_param('s', $xuid);
            $deletePrepare->execute();

            $insertPrepare = $db->prepare("INSERT INTO `ban` (`xuid`, `reason`, `timeReste`, `dateT`, ipv4) VALUES (?, ?, ?, ?, ?);");
            $insertPrepare->bind_param('ssiis', $xuid, $reason, $timeReste, $dateT, $ipv4);
            $insertPrepare->execute();
        }

        if (!isset($this->cache['mute'])) return;
        foreach ($this->cache['mute'] as $xuid => $values) {
            $xuid = $db->real_escape_string($xuid);
            $reason = $db->real_escape_string($values['reason']);
            $timeReste = intval($values['timeReste']);
            $dateT = intval($values['dateT']);

            $deletePrepare = $db->prepare("DELETE FROM `mute` WHERE `xuid` = ?;");
            $deletePrepare->bind_param('s', $xuid);
            $deletePrepare->execute();

            $insertPrepare = $db->prepare("INSERT INTO `mute` (`xuid`, `reason`, `timeReste`, `dateT`) VALUES (?, ?, ?, ?);");
            $insertPrepare->bind_param('ssii', $xuid, $reason, $timeReste, $dateT);
            $insertPrepare->execute();
        }

        if (!isset($this->cache['warn'])) return;
        foreach ($this->cache['warn'] as $xuid => $values) {
            $xuid = $db->real_escape_string($xuid);
            $warn = $db->real_escape_string(base64_encode(serialize($values)));

            $deletePrepare = $db->prepare("DELETE FROM `warn` WHERE `xuid` = ?;");
            $deletePrepare->bind_param('s', $xuid);
            $deletePrepare->execute();

            $insertPrepare = $db->prepare("INSERT INTO `warn` (`xuid`, `warn`) VALUES (?, ?);");
            $insertPrepare->bind_param('ss', $xuid, $warn);
            $insertPrepare->execute();
        }

        $db->close();


        if ($isClosed)$this->rechargeCache();
    }

    public function removeWarn(string $xuid, string $date): void
    {
        unset($this->cache['warn'][$xuid][$date]);
        $this->saveAllData();
    }

    /**
     * DATE => REASON
     * @param string $xuid
     * @return array
     */
    public function getAllWarns(string $xuid): array
    {
        return $this->cache['warn'][$xuid] ?? [];
    }

    public function mute(string $xuid, string $reason, int $time): void
    {
        $this->queue['mute'][$xuid] = ['reason' => $reason, 'timeReste' => time() + $time, 'dateT' => time()];
        $this->cache['mute'][$xuid] = ['reason' => $reason, 'timeReste' => time() + $time, 'dateT' => time()];
        $this->saveAllData();
    }

    public function unBan(string $xuid, Player $unbanner): void
    {
        if ($this->hasBan($xuid)) {
            unset($this->cache['ban'][$xuid]);
            if (isset($this->queue['ban'][$xuid])) unset($this->queue['ban'][$xuid]);
        }

        $array = SQL::informations();

        SQL::async(static function (RequestAsync $thread) use ($xuid, $array) {
            $db = new mysqli(
                $array['hostname'],
                $array['username'],
                $array['password'],
                $array['database'],
                $array['port']
            );
            $xuid = $db->real_escape_string($xuid);
            $select = $db->prepare("SELECT `xuid` FROM `ban` WHERE `xuid` = ?;");
            $select->bind_param('s', $xuid);
            $select->execute();
            $result = $select->get_result();
            $return = false;
            if ($result->num_rows > 0) {
                $return = true;
            }
            $delete = $db->prepare("DELETE FROM `ban` WHERE `xuid` = ?;");
            $delete->bind_param('s', $xuid);
            $delete->execute();
            $db->close();
            $thread->setResult($return);
        }, static function (RequestAsync $thread) use ($unbanner, $xuid) {
            $result = $thread->getResult();
            if (!$result) {
                $unbanner->sendMessage(Prefix::PREFIX_GOOD . "§cLe joueur est déjà unban !");
            } else $unbanner->sendMessage(Prefix::PREFIX_GOOD . "§aVous avez unban le joueur §f" . Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? "404" . "§a !");
        });
    }

    public function hasBan(string $xuid): bool
    {
        if (isset($this->cache['ban'][$xuid])) {
            if ($this->cache['ban'][$xuid]['timeReste'] <= time()) {
                unset($this->cache['ban'][$xuid]);
                return false;
            } else return true;
        }
        return false;
    }

    public function unMute(string $xuid, Player $unmuteur): void
    {
        if ($this->hasMute($xuid)) {
            unset($this->cache['mute'][$xuid]);
            if (isset($this->queue['mute'][$xuid])) unset($this->queue['mute'][$xuid]);
        }

        $array = SQL::informations();
        SQL::async(static function (RequestAsync $thread) use ($xuid, $array) {
            $db = new mysqli(
                $array['hostname'],
                $array['username'],
                $array['password'],
                $array['database'],
                $array['port']
            );
            $xuid = $db->real_escape_string($xuid);
            $select = $db->prepare("SELECT `xuid` FROM `mute` WHERE `xuid` = ?;");
            $select->bind_param('s', $xuid);
            $select->execute();
            $result = $select->get_result();
            $return = false;
            if ($result->num_rows > 0) {
                $result = true;
            }
            $delete = $db->prepare("DELETE FROM `mute` WHERE `xuid` = ?;");
            $delete->bind_param('s', $xuid);
            $delete->execute();
            $db->close();
            $thread->setResult($return);
        }, static function (RequestAsync $thread) use ($unmuteur, $xuid) {
            $result = $thread->getResult();
            if (!$result) {
                $unmuteur->sendMessage(Prefix::PREFIX_GOOD . "§cLe joueur est déjà unmute !");
            } else $unmuteur->sendMessage(Prefix::PREFIX_GOOD . "§aVous avez unmute le joueur §f" . Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? "404" . "§a !");
        });
    }

    public function hasMute(string $xuid): bool
    {
        if (isset($this->cache['mute'][$xuid])) {
            if ($this->cache['mute'][$xuid]['timeReste'] <= time()) {
                unset($this->cache['mute'][$xuid]);
                return false;
            } else return true;
        }
        return false;
    }

    public function getAllNameBanForArgs(): array
    {
        if (!isset($this->cache['ban'])) return [];
        $array = [];
        foreach ($this->cache['ban'] as $xuid => $values) {
            $name = $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
            if (!is_null($name)) {
                $array[($name = str_replace(' ', '_', strtolower($name)))] = $name;
            }
        }
        return $array;
    }

    public function getAllNameBanForArgsNoAssoc(): array
    {
        $array = [];
        if (!isset($this->cache['ban'])) return [];
        foreach ($this->cache['ban'] as $xuid => $values) {
            $name = $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
            if (!is_null($name)) {
                $array[] = $name;
            }
        }
        return $array;
    }

    public function getAllNameMuteForArgs(): array
    {
        $array = [];
        if (!isset($this->cache['mute'])) return [];
        foreach ($this->cache['mute'] as $xuid => $values) {
            $name = $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
            if (!is_null($name)) {
                $array[($name = str_replace(' ', '_', strtolower($name)))] = $name;
            }
        }
        return $array;
    }

    public function ban(string $xuid, string $reason, int $time): void
    {
        $ipv4 = Main::getInstance()->getDataManager()->getIpvByXuid($xuid);
        $this->queue['ban'][$xuid] = ['reason' => $reason, 'timeReste' => ($reste = time() + $time), 'dateT' => ($now = time()), 'ipv4' => ($ipv4 === null ? '404' : $ipv4)];
        $this->cache['ban'][$xuid] = ['reason' => $reason, 'timeReste' => $reste, 'dateT' => $now, 'ipv4' => ($ipv4 === null ? '404' : $ipv4)];
        $this->saveAllData();
        $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
        if (!is_null($player)) {
            $player->getServer()->getIPBans()->addBan($player->getNetworkSession()->getIp(), $reason, null, $player->getName());
        }

        if (!is_null($player)) {
            $msg = "§6Vous venez d'être bannie de GoldRush\n";
            $msg .= "§fReason: §6$reason\n";
            $msg .= "§fDate de fin: §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", $reste) . "\n";
            $msg .= "§fDiscord: §6https://discord.gg/goldrush";
            if ($player->isConnected()) $player->kick($msg,false);
        }
    }

    public function checkBanService(Player $player, callable $isAuthorized): void
    {
        $xuid = $player->getXuid();
        $ip = $player->getNetworkSession()->getIp();
        if (!$this->hasBan($xuid)) {
            SQL::async(static function (RequestAsync $complexThread, mysqli $db) use ($xuid, $ip) {
                $select = $db->prepare("SELECT * FROM `ban` WHERE `ipv4` = ?;");
                $ip = $db->real_escape_string($ip);
                $select->bind_param('s', $ip);
                $select->execute();
                $return = ['hasBan' => false, 'update' => null];
                $result = $select->get_result();
                if ($result->num_rows > 0) {
                    $assoc = $result->fetch_assoc();
                    if ($assoc['timeReste'] > time()) $return['hasBan'] = true;
                    $return['update'] = ['reason' => $assoc['reason'], 'timeReste' => $assoc['timeReste'], 'dateT' => $assoc['dateT'], 'ipv4' => $assoc['ipv4']];
                } else {
                    $select = $db->prepare("SELECT * FROM `ban` WHERE `xuid` = ?;");
                    $xuid = $db->real_escape_string($xuid);
                    $select->bind_param('s', $xuid);
                    $select->execute();
                    $result = $select->get_result();
                    if ($result->num_rows > 0) {
                        $assoc = $result->fetch_assoc();
                        if ($assoc['timeReste'] > time()) $return['hasBan'] = true;
                        $return['update'] = ['reason' => $assoc['reason'], 'timeReste' => $assoc['timeReste'], 'dateT' => $assoc['dateT'], 'ipv4' => $assoc['ipv4']];
                    }
                }
                $complexThread->setResult($return);
            }, static function (RequestAsync $thread) use ($xuid, $player, $isAuthorized) {
                if (!$player->isConnected()) return;
                $result = $thread->getResult();
                if ($result['hasBan']) {
                    $reason = $result['update']['reason'];
                    $reste = $result['update']['timeReste'];
                    $msg = "§6Vous venez d'être bannie de GoldRush\n";
                    $msg .= "§fReason: §6$reason\n";
                    $msg .= "§fDate de fin: §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", $reste) . "\n";
                    $msg .= "§fDiscord: §6https://discord.gg/gRnTe6tamt";
                    $player->kick($msg,false);
                    //    $this->getPlugin()->getScheduler()->scheduleRepeatingTask(new BanScheduler($player, $reason, $result), 20);
                } else {

                    Main::getInstance()->getConnexionManager()->connect($player, function (CustomPlayer $player) use ($isAuthorized) : void {
                        if (!$player->hasReallyConnected) {
                            $isAuthorized();
                            var_dump("connect one");
                        }
                    });
                }
                if ($result['update'] !== null) {
                   Main::getInstance()->getSanctionManager()->cache['ban'][$xuid] = $result['update'];
                }
                Main::getInstance()->getSanctionManager()->checkMuteServiceLoadCache($xuid);
            });
        } else {
            $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
            if (!is_null($player)) {
                $reason = $this->getReasonBan($xuid);
                $reste = time() + $this->getBanTimeReste($xuid);
                $msg = "§6Vous venez d'être bannie de GoldRush\n";
                $msg .= "§fReason: §6$reason\n";
                $msg .= "§fDate de fin: §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", $reste) . "\n";
                $msg .= "§fDiscord: §6https://discord.gg/gRnTe6tamt";
                $player->kick($msg,false);
                //$this->getPlugin()->getScheduler()->scheduleRepeatingTask(new BanScheduler($player, $reason, $reste), 20);
            }
        }
    }

    public function checkMuteServiceLoadCache(string $xuid): void
    {
        SQL::async(static function(RequestAsync $thread, mysqli $db) use ($xuid): void {
            $select = $db->prepare("SELECT * FROM `mute` WHERE `xuid` = ?");
            $xuid = $db->real_escape_string($xuid);
            $select->bind_param('s', $xuid);
            $select->execute();
            $return = null;
            $result = $select->get_result();
            if ($result->num_rows > 0) {
                $assoc = $result->fetch_assoc();
                $return = ['reason' => $assoc['reason'], 'timeReste' => $assoc['timeReste'], 'dateT' => $assoc['dateT']];
            }
            $thread->setResult($return);
        }, static function(RequestAsync $thread) use ($xuid) : void {
            $result = $thread->getResult();
            if (!is_null($result)) {
                Main::getInstance()->getSanctionManager()->cache['mute'][$xuid] = $result;
            }
        });
    }

    public function getReasonBan(string $xuid): string
    {
        return $this->cache['ban'][$xuid]['reason'];
    }

    public function getBanTimeReste(string $xuid): int
    {
        return $this->cache['ban'][$xuid]['timeReste'] - time();
    }

    public function getReasonMute(string $xuid): string
    {
        return $this->cache['mute'][$xuid]['reason'];
    }

    public function getMuteTimeReste(string $xuid): int
    {
        return $this->cache['mute'][$xuid]['timeReste'] - time();
    }
}