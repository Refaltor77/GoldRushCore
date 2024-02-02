<?php

namespace core\managers\homes;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\player\CustomPlayer;
use core\sql\SQL;
use core\traits\HomeTrait;
use mysqli;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

class HomeManager extends Manager
{
    use HomeTrait;

    public static array $cache = [];
    public static array $cacheFastInterServer = [];
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = Main::getInstance();
        $db = SQL::connection();
        $db->prepare("CREATE TABLE IF NOT EXISTS `homes` (`xuid` VARCHAR(255) PRIMARY KEY, `homes` TEXT, `teleportation` VARCHAR(255))")->execute();
        $db->close();

        parent::__construct($plugin);
    }




    public function getAllHomes(Player $player, callable $callback): void {
        $allHomes =  self::$cache[$player->getXuid()];

        $array = [];
        foreach ($allHomes as $homeName => $pos) {
            $array[] = $homeName;
        }
        $callback($player, $array);
    }




    public function getHomeCount(Player $player): int {
        return count(self::$cache[$player->getXuid()]);
    }

    public function loadData(Player|CustomPlayer $player): void
    {
        $xuid = $player->getXuid();


        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid) {
            $select = $db->prepare("SELECT * FROM `homes` WHERE `xuid` = ?;");
            $xuid = $db->real_escape_string($xuid);
            $select->bind_param('s', $xuid);
            $select->execute();
            $result = $select->get_result();
            $return = ['tp' => null, 'homes' => []];
            if ($result->num_rows > 0) {
                $assoc = $result->fetch_assoc();
                if ($assoc['teleportation'] === 'NULL') $assoc['teleportation'] = null;
                $return['tp'] = $assoc['teleportation'];
                $return['homes'] = unserialize(base64_decode($assoc['homes']));
            }
            $thread->setResult($return);
        }, static function (RequestAsync $thread) use ($xuid, $player) {
            $result = $thread->getResult();
            if (!$player->isConnected()) return;
            $player->hasHomeLoaded = true;
            if (!is_null($result['tp'])) {
                $parserLocation = explode(':', $result['tp']);
                $world = Server::getInstance()->getWorldManager()->getWorldByName($parserLocation[3]);
                if ($world === null) $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
                $x = intval($parserLocation[0]);
                $y = intval($parserLocation[1]);
                $z = intval($parserLocation[2]);
                $player->teleport(new Position($x, $y, $z, $world));
                $player->sendMessage("§eTéléportation effectuée !", true, success: true);
            }
            self::$cache[$xuid] = ($result['homes'] === null ? [] : $result['homes']);
        });
    }



    public function getAllHomesDisconnected(string $xuid, callable $callback): void {
        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid) {
            $select = $db->prepare("SELECT * FROM `homes` WHERE `xuid` = ?;");
            $xuid = $db->real_escape_string($xuid);
            $select->bind_param('s', $xuid);
            $select->execute();
            $result = $select->get_result();
            $return = ['tp' => null, 'homes' => []];
            if ($result->num_rows > 0) {
                $assoc = $result->fetch_assoc();
                if ($assoc['teleportation'] === 'NULL') $assoc['teleportation'] = null;
                $return['tp'] = $assoc['teleportation'];
                $return['homes'] = unserialize(base64_decode($assoc['homes']));
            }
            $thread->setResult($return);
        }, static function (RequestAsync $thread) use ($xuid, $callback) {
            $result = $thread->getResult();
            $callback($result['homes']);
        });
    }

    public function getAllHomesForArgs(string $xuid): array
    {
        if (isset(self::$cache[$xuid])) {
            $array = [];
            foreach (self::$cache[$xuid] as $homeName => $values) {
                if (str_contains(' ', $homeName)) {
                    $array[] = '"' . strtolower($homeName) . '"';
                } else $array[] = strtolower($homeName);
            }
        } else return [];
        return $array;
    }

    public function getHomeInfo(string $xuid, string $homeName): string
    {
        $parse = $this->parseString(self::$cache[$xuid][$homeName]);
        return $parse[0] . ':' . $parse[1] . ':' . $parse[2] . ':' . $parse[3];
    }

    public function parseString(string $posHash): array
    {
        return explode(':', $posHash);
    }

    public function getAllHomesPlayer(Player $player): array {
        $homes = [];

        if (!isset(self::$cache[$xuid = $player->getXuid()])) {
            self::$cache[$xuid] = [];
        }

        foreach (self::$cache[$player->getXuid()] as $homeName => $posHash) {
            $homes[$homeName] = $posHash;
        }
        return $homes;
    }

    public function setHome(string $xuid, string $homeName, Position $pos): void
    {
        if (!isset(self::$cache[$xuid])) {
            self::$cache[$xuid] = [];
        }

        self::$cache[$xuid][$homeName] = intval($pos->getX()) . ':' . intval($pos->getY()) . ':' . intval($pos->getZ()) . ':' . strval($pos->getWorld()->getFolderName()) . ':' . Main::XUID_SERVER;
        $data = $this->getPlugin()->getDataManager();
        if ($data->isConnectedXuid($xuid)) {
            $player = $data->getPlayerXuid($xuid);
            if ($player instanceof CustomPlayer) {
                $this->saveData($player, null, false);
            }
        }
    }

    public function getServerXuidHome(string $xuid, string $homeName): string
    {
        $array = $this->parseString(self::$cache[$xuid][$homeName]);
        return $array[4];
    }

    public function deleteHome(string $xuid, string $homeName): void
    {
        unset(self::$cache[$xuid][$homeName]);
        $data = $this->getPlugin()->getDataManager();
        if ($data->isConnectedXuid($xuid)) {
            $player = $data->getPlayerXuid($xuid);
            if ($player instanceof CustomPlayer) {
                $this->saveData($player, null, false);
            }
        }
    }

    public function hasHome(string $xuid, string $homeName): bool
    {

        return isset(self::$cache[$xuid][$homeName]);
        /*foreach (self::self::$$cache[$xuid] as $home) {
            if (strtolower($home) === strtolower($homeName)) {
                return true;
            }
        }*/
    }

    public function getPosHome(string $xuid, string $homeName): Position
    {
        $pos = $this->parseString(self::$cache[$xuid][$homeName]);
        return new Position(
            intval($pos[0]),
            intval($pos[1]),
            intval($pos[2]),
            Server::getInstance()->getWorldManager()->getWorldByName($pos[3])
        );
    }

    public function saveData(Player $player, ?string $tpInterServer = null, bool $clear = true): void
    {
        $xuid = $player->getXuid();
        if (empty(self::$cache[$xuid])) return;
        $homes = base64_encode(serialize(self::$cache[$xuid]));
        $tp = 'NULL';
        if (!is_null($tpInterServer)) {
            $tp = $tpInterServer;
            if (isset(self::$cacheFastInterServer[$player->getXuid()])) unset(self::$cacheFastInterServer[$player->getXuid()]);
        }
        if ($clear) unset(self::$cache[$xuid]);
        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $homes, $tp) {
            $xuid = $db->real_escape_string($xuid);
            $homes = $db->real_escape_string($homes);
            $insert = $db->prepare("INSERT INTO `homes` (`xuid`, `homes`, `teleportation`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE homes = VALUES(homes), teleportation = VALUES(teleportation);");
            $null = $db->real_escape_string('NULL');
            $insert->bind_param('sss', $xuid, $homes, $null);
            $insert->execute();
        });
    }

    public function saveDataNotAsync(Player $player): void
    {
        $xuid = $player->getXuid();
        if (!isset(self::$cache[$xuid])) {
            return;
        }
        if (empty(self::$cache[$xuid])) return;
        $homes = base64_encode(serialize(self::$cache[$xuid]));
        $db = SQL::connection();
        $xuid = $db->real_escape_string($xuid);
        $homes = $db->real_escape_string($homes);
        $insert = $db->prepare("INSERT INTO `homes` (`xuid`, `homes`, `teleportation`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE homes = VALUES(homes), teleportation = VALUES(teleportation);");
        $null = $db->real_escape_string('NULL');
        $insert->bind_param('sss', $xuid, $homes, $null);
        $insert->execute();
        $db->close();
    }
}