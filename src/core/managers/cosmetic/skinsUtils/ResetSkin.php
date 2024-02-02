<?php

namespace core\managers\cosmetic\skinsUtils;

use core\Main;
use pocketmine\entity\Skin;
use pocketmine\player\Player;

class resetSkin
{


    public function setSkin(Player $player)
    {
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Main::getInstance()->getDataFolder() . "saveskin/" . $name . ".png";
        if(!file_exists($path)){
            $path = Main::getInstance()->getDataFolder()."steve.png";
        }
        $img = @imagecreatefrompng($path);
        $size = getimagesize($path);
        $skinbytes = "";
        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $x < $size[0]; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~($colorat >> 24)) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry.humanoid.custom", file_get_contents(Main::getInstance()->getDataFolder() . "steve.json")));
        $player->sendSkin();
    }
}