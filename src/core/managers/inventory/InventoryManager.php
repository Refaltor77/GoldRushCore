<?php

namespace core\managers\inventory;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use core\utils\Utils;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EffectIds;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\Server;

class InventoryManager extends Manager
{
    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
        $db = $this->getConnexion()->connect();
        $db->query("CREATE TABLE IF NOT EXISTS `inventory` (`xuid` VARCHAR(255) PRIMARY KEY, `simpleInventory` LONGTEXT, `enderInventory` LONGTEXT, `armorInventory` LONGTEXT, `offHandInventory` LONGTEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS `xp` (`xuid` VARCHAR(255) PRIMARY KEY, xp INT);");
        $db->query("CREATE TABLE IF NOT EXISTS effects (xuid VARCHAR(255) PRIMARY KEY, data LONGTEXT);");
        $db->close();
    }

    public function checkingDatabase(Player $player, ?callable $callback = null): void {
        $xuid = $player->getXuid();
        SQL::async(static function (RequestAsync $closure, \mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM `inventory` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_assoc();


            $inventory64 = [];
            $armorInventory64 = [];
            $offHandInventory64 = [];
            $enderInventory64 = [];

            if (isset($result['simpleInventory']) && isset($result['armorInventory']) && isset($result['offHandInventory']) && isset($result['enderInventory'])) {
                $inventory64 = unserialize(base64_decode($result['simpleInventory']));
                $armorInventory64 = unserialize(base64_decode($result['armorInventory']));
                $offHandInventory64 = unserialize(base64_decode($result['offHandInventory']));
                $enderInventory64 = unserialize(base64_decode($result['enderInventory']));
            }


            $prepare = $db->prepare("SELECT * FROM `xp` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_assoc();
            $xp = 0;
            if (isset($result['xp'])) {
                $xp = $result['xp'];
            }

            $prepare = $db->prepare("SELECT * FROM `effects` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_assoc();
            $effects = [];
            if (isset($result['data'])) {
                $effects = unserialize(base64_decode($result['data']));
            }


            $closure->setResult([$inventory64, $armorInventory64, $offHandInventory64, $enderInventory64, $xp, $effects]);


        }, static function (RequestAsync $closure) use ($player, $callback): void {
            if ($player->isConnected()) {
                $result = $closure->getResult();
                if (!is_null($result)) {

                    $player->hasInvLoaded = true;
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->getOffHandInventory()->clearAll();
                    $player->getEnderInventory()->clearAll();
                    $player->getXpManager()->setCurrentTotalXp(0);


                    foreach ($result[0] as $slot => $itemSerialized) {
                        $player->getInventory()->setItem($slot, Utils::unserializeItem($itemSerialized));
                    }
                    foreach ($result[1] as $slot => $itemSerialized) {
                        $player->getArmorInventory()->setItem($slot, Utils::unserializeItem($itemSerialized));
                    }
                    foreach ($result[2] as $slot => $itemSerialized) {
                        $player->getOffHandInventory()->setItem($slot, Utils::unserializeItem($itemSerialized));
                    }
                    foreach ($result[3] as $slot => $itemSerialized) {
                        $player->getEnderInventory()->setItem($slot, Utils::unserializeItem($itemSerialized));
                    }

                    if (isset($result[4])) {
                        $player->getXpManager()->setCurrentTotalXp($result[4]);
                    }

                    if (isset($result[5])) {

                        foreach ($result[5] as $idEffect => $values) {
                            $effect = EffectIdMap::getInstance()->fromId($idEffect);
                            $player->getEffects()->add(new EffectInstance($effect, $values['duration'],  $values['amplifier']));
                        }
                    }
                }
                if (!is_null($callback)) $callback($player);
            }
        });
    }










    public function checkingDatabasePlayerXuid(string $player, ?callable $callback = null): void {
        $xuid = $player;
        SQL::async(static function (RequestAsync $closure, \mysqli $db) use ($xuid): void {
            $prepare = $db->prepare("SELECT * FROM `inventory` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_assoc();


            $inventory64 = [];
            $armorInventory64 = [];
            $offHandInventory64 = [];
            $enderInventory64 = [];

            if (isset($result['simpleInventory']) && isset($result['armorInventory']) && isset($result['offHandInventory']) && isset($result['enderInventory'])) {
                $inventory64 = unserialize(base64_decode($result['simpleInventory']));
                $armorInventory64 = unserialize(base64_decode($result['armorInventory']));
                $offHandInventory64 = unserialize(base64_decode($result['offHandInventory']));
                $enderInventory64 = unserialize(base64_decode($result['enderInventory']));
            }



            $closure->setResult([$inventory64, $armorInventory64, $offHandInventory64, $enderInventory64]);


        }, static function (RequestAsync $closure) use ($callback): void {
                $result = $closure->getResult();


                $inventory64 = [];
                $armorInventory64 = [];
                $offHandInventory64 = [];
                $enderInventory64 = [];


                if (!is_null($result)) {

                    foreach ($result[0] as $slot => $itemSerialized) {
                        $inventory64[] = Utils::unserializeItem($itemSerialized);
                    }
                    foreach ($result[1] as $slot => $itemSerialized) {
                        $armorInventory64[] = Utils::unserializeItem($itemSerialized);
                    }
                    foreach ($result[2] as $slot => $itemSerialized) {
                        $offHandInventory64[] = Utils::unserializeItem($itemSerialized);
                    }
                    foreach ($result[3] as $slot => $itemSerialized) {
                        $enderInventory64[] = Utils::unserializeItem($itemSerialized);
                    }
                }

                $callback($inventory64, $armorInventory64, $offHandInventory64, $enderInventory64);
        });
    }







    public function saveInventory(Player $player, bool $async = true, bool $clear = true, ?callable $callback = null): void {
        if (!$player->hasReallyConnected) return;
        if (Main::getInstance()->getStaffManager()->isInStaffMode($player)) return;
        $arrayQueried = [0 => [], 1 => [], 2 => [], 3 => []];
        foreach ($player->getInventory()->getContents() as $slot => $item) {
            $arrayQueried[0][$slot] = Utils::serilizeItem($item);
        }
        foreach ($player->getArmorInventory()->getContents() as $slot => $item) {
            $arrayQueried[1][$slot] = Utils::serilizeItem($item);
        }
        foreach ($player->getOffHandInventory()->getContents() as $slot => $item) {
            $arrayQueried[2][$slot] = Utils::serilizeItem($item);
        }
        foreach ($player->getEnderInventory()->getContents() as $slot => $item) {
            $arrayQueried[3][$slot] = Utils::serilizeItem($item);
        }

        $xp = $player->getXpManager()->getCurrentTotalXp();
        $arrayQueried[4] = $xp;


        $xuid = $player->getXuid();

        $effects = [];
        foreach ($player->getEffects()->all() as $effectInstance) {
            $effect = $effectInstance->getType();
            $id = EffectIdMap::getInstance()->toId($effect);
            if (in_array($id, [
                EffectIds::HASTE,
                EffectIds::SLOWNESS,
                EffectIds::ABSORPTION,
                EffectIds::BLINDNESS,
                EffectIds::HUNGER
            ])) {
                $effects[$id] = [
                    'duration' => $effectInstance->getDuration(),
                    'amplifier' => $effectInstance->getAmplifier()
                ];
            }
        }

        if ($async) {
            $this->getConnexion()->processRequestSQL(static function (RequestAsync $closure, \mysqli $db) use ($arrayQueried, $xuid, $effects) : void {

                $inv64 = base64_encode(serialize($arrayQueried[0]));
                $armor64 = base64_encode(serialize($arrayQueried[1]));
                $offHand64 = base64_encode(serialize($arrayQueried[2]));
                $ender64 = base64_encode(serialize($arrayQueried[3]));
                $effects64 = base64_encode(serialize($effects));
                $prepare = $db->prepare("INSERT INTO `inventory` (`xuid`, `simpleInventory`, `armorInventory`, `offHandInventory`, `enderInventory`) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE simpleInventory = VALUES(simpleInventory), armorInventory = VALUES(armorInventory), offHandInventory = VALUES(offHandInventory), enderInventory = VALUES(enderInventory);");
                $prepare->bind_param('sssss', $xuid, $inv64, $armor64, $offHand64, $ender64);
                $prepare->execute();

                $xp = $arrayQueried[4];
                $prepare = $db->prepare("INSERT INTO `xp` (`xuid`, `xp`) VALUES (?, ?) ON DUPLICATE KEY UPDATE xp = VALUES(xp);");
                $prepare->bind_param('si', $xuid, $xp);
                $prepare->execute();

                $prepare = $db->prepare("INSERT INTO effects (xuid, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
                $prepare->bind_param('ss', $xuid, $effects64);
                $prepare->execute();
            }, static function (RequestAsync $async) use ($callback): void {
                if (!is_null($callback)) $callback();
            });
        } else {
            $db = $this->getConnexion()->connect();
            $inv64 = base64_encode(serialize($arrayQueried[0]));
            $armor64 = base64_encode(serialize($arrayQueried[1]));
            $offHand64 = base64_encode(serialize($arrayQueried[2]));
            $ender64 = base64_encode(serialize($arrayQueried[3]));
            $prepare = $db->prepare("INSERT INTO `inventory` (`xuid`, `simpleInventory`, `armorInventory`, `offHandInventory`, `enderInventory`) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE simpleInventory = VALUES(simpleInventory), armorInventory = VALUES(armorInventory), offHandInventory = VALUES(offHandInventory), enderInventory = VALUES(enderInventory);");
            $prepare->bind_param('sssss', $xuid, $inv64, $armor64, $offHand64, $ender64);
            $prepare->execute();

            $xp = $arrayQueried[4];
            $prepare = $db->prepare("INSERT INTO `xp` (`xuid`, `xp`) VALUES (?, ?) ON DUPLICATE KEY UPDATE xp = VALUES(xp);");
            $prepare->bind_param('si', $xuid, $xp);
            $prepare->execute();

            $effects64 = base64_encode(serialize($effects));
            $prepare = $db->prepare("INSERT INTO effects (xuid, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data);");
            $prepare->bind_param('ss', $xuid, $effects64);
            $prepare->execute();

            $db->close();
        }
    }


    public function saveAllData(array $players): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $this->saveInventory($player, false);
        }
    }
}