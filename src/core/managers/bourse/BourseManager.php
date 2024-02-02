<?php

namespace core\managers\bourse;

use core\Main;
use core\managers\Manager;
use core\settings\Ids;
use core\sql\SQL;
use pocketmine\utils\Config;

class BourseManager extends Manager
{
    private array $bourse = [];

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS bourse(data TEXT);");


        $query = $db->query("SELECT * FROM bourse;")->fetch_assoc();



        $query = unserialize(base64_decode($query['data'] ?? base64_encode(serialize([]))));

        $this->bourse = $query ?? [
                'minecraft:melon_slice' => mt_rand(2, 4),
                'minecraft:potatoes' => mt_rand(3, 8),
                'minecraft:carrots' => mt_rand(3, 8),
                'minecraft:wheat' => mt_rand(6, 12),
                'minecraft:beetroots' => mt_rand(6, 12),
                'minecraft:pumpkin' => mt_rand(4, 6),
                'minecraft:sugarcane' => mt_rand(4, 6),
                'minecraft:cactus' => mt_rand(3, 5),
                Ids::RAISIN => mt_rand(6, 12),
                Ids::BERRY_BLUE => mt_rand(6, 12),
                Ids::BERRY_PINK => mt_rand(6, 12),
                Ids::BERRY_YELLOW => mt_rand(6, 12),
                Ids::BERRY_BLACK => mt_rand(6, 12),
            ];

        parent::__construct($plugin);
    }


    public function setBourse(array $bourse): void  {
        $this->bourse = $bourse;

        $bourse = $this->getBourse();

        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $sell = $config->get('shop');
        foreach ($sell['Farming']['items'] as $index => $values) {
            $id = $values['idMeta'];
            if (in_array($id, array_keys($bourse))) {
                if (isset($values['sell'])) {
                    $sell['Farming']['items'][$index]['sell'] = $bourse[$id];
                }
            }
        }

        $config->set('shop', $sell);
        $config->save();
    }

    public function getBourse(): array {
        return $this->bourse;
    }

    public function saveAllData(): void {
        $bourse = base64_encode(serialize($this->bourse));
        $db = SQL::connection();
        $db->query("DELETE FROM bourse;");
        $db->query("INSERT INTO bourse (`data`) VALUES ('$bourse');");
        $db->close();
    }
}