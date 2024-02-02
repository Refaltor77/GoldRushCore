<?php

namespace core\managers\skins;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use mysqli;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\Server;

class SkinManager2 extends Manager
{
    public array $cache = [];

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS skins (xuid VARCHAR(255) PRIMARY KEY, skin LONGTEXT);");
        $db->close();
        parent::__construct($plugin);
    }

    public function loadData(Player $player): void {
        $xuid = $player->getXuid();
        SQL::async(static function(RequestAsync $async, mysqli $db) use ($xuid) : void {
            $query = $db->query("SELECT * FROM skins WHERE xuid = '$xuid';");
            $skin = [];
            if ($query->num_rows > 0) {
                $skin = unserialize(base64_decode($query->fetch_assoc()['skin']));
            }
            $async->setResult($skin);
        }, static function(RequestAsync $async) use ($player): void {
            if (!$player->isConnected()) return;
            $skin = $async->getResult();
            if ($skin === []) {
                $skin = $player->getSkin();
            } else {
                $skin = new Skin(
                    $skin['id'],
                    $skin['skin_data'],
                    $skin['cape_data'],
                    $skin['geometry_name'],
                    $skin['geometry_data']
                );
            }

            Main::getInstance()->getSkinManager2()->cache[$player->getXuid()] = $skin;
        });
    }

    public function getSkinPlayer(string $xuid): ?Skin {
        return $this->cache[$xuid] ?? null;
    }


    public function saveData(Player $player, bool $async = true): void
    {
        $skin = $player->getSkin();

        $skinData = [
            'id' => $skin->getSkinId(),
            'skin_data' => $skin->getSkinData(),
            'cape_data' => $skin->getCapeData(),
            'geometry_name' => $skin->getGeometryName(),
            'geometry_data' => $skin->getGeometryData()
        ];

        $skinData = base64_encode(serialize($skinData));
        $xuid = $player->getXuid();


        if ($async) {
            SQL::async(static function (RequestAsync $async, mysqli $db) use ($skinData, $xuid): void {
                $prepare = $db->prepare("INSERT INTO skins (xuid, skin) VALUES (? ,?) ON DUPLICATE KEY UPDATE skin = VALUES (skin);");
                $prepare->bind_param('ss', $xuid, $skinData);
                $prepare->execute();
            });
        } else {
            $db = SQL::connection();
            $prepare = $db->prepare("INSERT INTO skins (xuid, skin) VALUES (? ,?) ON DUPLICATE KEY UPDATE skin = VALUES (skin);");
            $prepare->bind_param('ss', $xuid, $skinData);
            $prepare->execute();
            $db->close();
        }
    }


    public function saveAllData(): void {
        $db = SQL::connection();

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $skin = $player->getSkin();

            $skinData = [
                'id' => $skin->getSkinId(),
                'skin_data' => $skin->getSkinData(),
                'cape_data' => $skin->getCapeData(),
                'geometry_name' => $skin->getGeometryName(),
                'geometry_data' => $skin->getGeometryData()
            ];

            $skinData = base64_encode(serialize($skinData));



            $xuid = $player->getXuid();
            $prepare = $db->prepare("INSERT INTO skins (xuid, skin) VALUES (? ,?) ON DUPLICATE KEY UPDATE skin = VALUES (skin);");
            $prepare->bind_param('ss', $xuid, $skinData);
            $prepare->execute();
        }
        $db->close();
    }
}