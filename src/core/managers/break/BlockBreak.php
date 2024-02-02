<?php

namespace core\managers\break;

use core\async\RequestAsync;
use core\Main;
use core\managers\factions\FactionManager;
use core\managers\Manager;
use core\sql\SQL;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;

class BlockBreak extends Manager
{
    private array $cache = [];
    private array $itemQuestFaction = [];

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS block_break (xuid VARCHAR(255) PRIMARY KEY, data LONGTEXT)");
        $db->close();

        foreach (FactionManager::QUEST as $index => $itemString) {
            if (str_contains($itemString, 'goldrush:')) {
                $this->itemQuestFaction[] = CustomiesItemFactory::getInstance()->get($itemString)->getTypeId();
            } else {
                $this->itemQuestFaction[] = StringToItemParser::getInstance()->parse($itemString)->getTypeId();
            }
        }
        parent::__construct($plugin);
    }

    public function loadData(Player $player, callable $callback): void {
        $xuid = $player->getXuid();
        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid) : void {
            $query = $db->query("SELECT * FROM block_break WHERE xuid = '$xuid';");
            $return = [];
            if ($query->num_rows > 0) {
                $data = $query->fetch_assoc();
                $return = unserialize(base64_decode($data['data']));
            }
            $async->setResult($return);
        }, static function(RequestAsync $async) use ($player, $callback): void {
            if ($player->isConnected()) {
                $data = $async->getResult();
                $dataQueried = [];
                foreach ($data as $index => $itemString) {
                    $dataQueried[] = Utils::unserializeItem($itemString);
                }
                Main::getInstance()->getBlockBreakManager()->cache[$player->getXuid()] = $dataQueried;
                $callback($player);
            }
        });
    }


    public function checkupFactionQuest(Player $player): void {
        $xuid = $player->getXuid();
        if (!isset($this->cache[$xuid])) return;
        $cache = $this->cache[$xuid];
        if (empty($cache)) return;


        $itemFac = Main::getInstance()->getFactionManager()->getItemQuest();
        foreach ($cache as $index => $itemCheck) {
            if ($itemFac->getTypeId() === $itemCheck->getTypeId()) {
                if (Main::getInstance()->getFactionManager()->isInFaction($player->getXuid())) {
                    Main::getInstance()->getFactionManager()->addItemQuestFaction($player, 1);
                }
            }
        }


        $this->cache[$player->getXuid()] = [];
        $dataQueried = [];
        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid, $dataQueried): void {
            $dataQueried = base64_encode(serialize($dataQueried));
            $db->query("INSERT INTO block_break (xuid, data) VALUES ('$xuid', '$dataQueried') ON DUPLICATE KEY UPDATE data = VALUES(data); ");
        });
    }


    public function addItemBreak(Player $player, Item $item): void {
        if (in_array($item->getTypeId(), $this->itemQuestFaction)) {
            if (isset($this->cache[$player->getXuid()])) $this->cache[$player->getXuid()][] = $item;
        }
    }


    public function saveData(Player $player): void {
        $xuid = $player->getXuid();
        $data = $this->cache[$xuid];
        $dataQueried = [];
        if (empty($data)) return;
        foreach ($data as $index => $item) {
            if ($item instanceof Item) {
                $dataQueried[] = Utils::serilizeItem($item);
            }
        }
        $dataQueried = base64_encode(serialize($dataQueried));
        unset($this->cache[$xuid]);
        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid, $dataQueried): void {
            $db->query("INSERT INTO block_break (xuid, data) VALUES ('$xuid', '$dataQueried') ON DUPLICATE KEY UPDATE data = VALUES(data); ");
        });
    }
}