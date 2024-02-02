<?php

namespace core\entities;

use core\entities\Text;
use core\Main;
use core\settings\Ids;
use core\unicodes\FarmUnicode;
use core\unicodes\TextUnicode;
use pocketmine\utils\Config;

class BourseText extends Text
{
    private int $time = 0;



    public function onUpdate(int $currentTick): bool
    {

        if ($this->time === 20 * 10) {
            $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
            $sell = $config->get('shop');


            $ids = [
                'minecraft:melon_slice' => FarmUnicode::MELON . '  §f................................  §f{price}§6$',
                'minecraft:potatoes' => FarmUnicode::POTATO . ' §f................................  §f{price}§6$',
                'minecraft:carrots' => FarmUnicode::CARROT . ' §f................................  §f{price}§6$',
                'minecraft:wheat' => FarmUnicode::WHEAT . ' §f................................  §f{price}§6$',
                'minecraft:pumpkin' => FarmUnicode::CITROUILLE . ' §f................................  §f{price}§6$',
                'minecraft:sugarcane' => FarmUnicode::SUGARCANE . ' §f................................  §f{price}§6$',
                'minecraft:cactus' => FarmUnicode::CACTUS . ' §f................................  §f{price}§6$'
            ];


            $space = "\n";
            $msg = TextUnicode::LIGNE . " §lBOURSE §r§f" . TextUnicode::LIGNE . $space;

            foreach ($sell['Farming']['items'] as $index => $values) {
                $id = $values['idMeta'];
                if (in_array($id, array_keys($ids))) {
                    if (isset($values['sell'])) {
                        $msg .= str_replace('{price}', $values['sell'], $ids[$id]) . "\n\n";
                    }
                }
            }

            $msg .= "§f-------------------";
            $this->nameFloating = $msg;
            $this->time = 0;
        }
        $this->time++;
        return parent::onUpdate($currentTick);
    }
}