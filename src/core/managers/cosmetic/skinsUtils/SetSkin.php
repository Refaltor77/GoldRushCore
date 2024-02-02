<?php

namespace core\managers\cosmetic\skinsUtils;

use core\Main;
use pocketmine\entity\Skin;
use pocketmine\player\Player;

class setSkin
{



    public function setSkin(Player $player, string $stuffName)
    {
        $locate = "cosmetic";
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Main::getInstance()->getDataFolder() . "saveskin/" . $name . ".png";

        // Charger la géométrie du chapeau
        $geometry = file_get_contents(Main::getInstance()->getDataFolder() . $locate . "/" . $stuffName . ".json");

        // Charger le skin du joueur
        $originalSkin = $skin;

        // Lire la taille de l'image
        $size = getimagesize($path);

        // Modifier seulement la partie du skin qui correspond au chapeau
        $path = $this->imgTricky($path, $stuffName, $locate, [$size[0], $size[1], 4]);
        $img = @imagecreatefrompng($path);
        $skinbytes = "";
        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $size[0]; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);

        // Créer un nouveau Skin avec la géométrie du chapeau
        $newSkin = new Skin($skin->getSkinId(), $skinbytes,"", "geometry.npc.steve", $geometry);

        // Appliquer le nouveau Skin au joueur
        $player->setSkin($newSkin);
        $player->sendSkin();
    }



    public function imgTricky(string $skinPath, string $stuffName, string $locate, array $size)
    {
        $path = Main::getInstance()->getDataFolder();
        $down = imagecreatefrompng($skinPath);
        $upper = null;
        if ($size[0] * $size[1] * $size[2] == 65536) {
            $upper = $this->resize_image($path . $locate . "/" . $stuffName . ".png", 128, 128);
        } else {
            $upper = $this->resize_image($path . $locate . "/" . $stuffName . ".png", 64, 64);
        }
        //Remove black color out of the png
        imagecolortransparent($upper, imagecolorallocatealpha($upper, 0, 0, 0, 127));

        imagealphablending($down, true);
        imagesavealpha($down, true);
        imagecopymerge($down, $upper, 0, 0, 0, 0, $size[0], $size[1], 100);
        imagepng($down, $path . 'temp.png');
        return Main::getInstance()->getDataFolder() . 'temp.png';

    }

    public function resize_image($file, $w, $h, $crop = FALSE)
    {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }
        $src = imagecreatefrompng($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return $dst;
    }
}