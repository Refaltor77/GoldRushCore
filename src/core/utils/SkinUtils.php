<?php

namespace core\utils;

use core\Main;
use GdImage;
use JsonException;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\utils\BinaryStream;

class SkinUtils
{
    public static array $acceptedSkinSize = [
        64 * 32 * 4,
        64 * 64 * 4,
        128 * 128 * 4
    ];
    public static array $skin_widght_map = [
        64 * 32 * 4 => 64,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128
    ];

    public static array $skin_height_map = [
        64 * 32 * 4 => 32,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128
    ];

    public static function saveSkin(Skin $skin, $name)
    {
        $path = Main::getInstance()->getDataFolder();
        if (!file_exists($path . "saveskin")) {
            mkdir($path . "saveskin");
        }
        $img = self::skinDataToImage($skin->getSkinData());
        if ($img == null) {
            return;
        }
        imagepng($img, $path . "saveskin/" . $name . ".png");
    }

    /**
     * @throws JsonException
     */
    public static function getSkinFromFile(string $path): ?Skin
    {
        $img = @imagecreatefrompng($path);
        $bytes = '';
        $l = (int)@getimagesize($path)[1];
        for ($y = 0; $y < $l; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~(($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return new Skin("Standard_CustomSlim", $bytes);
    }
    //Changes the whole skin || Example: Suits, Morphs
    /**
     * @throws JsonException
     */
    public static function setSkin(Player $player, string $stuffName, string $locate)
    {
        $skin = $player->getSkin();
        $path = Main::getInstance()->getDataFolder() . $locate . "/" . $stuffName . ".png";
        $size = getimagesize($path);

        $img = @imagecreatefrompng($path);
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
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry." . $locate, file_get_contents(Main::getInstance()->getDataFolder() . $locate . "/" . $stuffName . ".json")));
        $player->sendSkin();
    }



    public static function setWings(Player $player, string $stuffName)
    {
        $locate = "cosmetic/wings/";
        $skin = $player->getSkin();
        $name = $player->getXuid();
        $path = Main::getInstance()->getDataFolder() . "saveskin/" . $name . ".png";
        if (!file_exists($path)) {
            $path = Main::getInstance()->getDataFolder() . "steve.png";
        }
        $size = getimagesize($path);

        $path = self::imgTricky($path, $stuffName, $locate, [$size[0], $size[1], 4]);
        $img = @imagecreatefrompng($path);
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
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry.wings", file_get_contents(Main::getInstance()->getDataFolder() . "cosmetic/wings.geo.json")));
        $player->sendSkin();
    }


    /**
     * @throws JsonException
     */
    public static function setSkinOnTop(Player $player, string $stuffName, string $locate)
    {
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Main::getInstance()->getDataFolder() . "saveskin/" . $name . ".png";
        if (!file_exists($path)) {
            $path = Main::getInstance()->getDataFolder() . "steve.png";
        }
        $size = getimagesize($path);

        $path = self::imgTricky($path, $stuffName, $locate, [$size[0], $size[1], 4]);
        $img = @imagecreatefrompng($path);
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
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry.cowboy", file_get_contents(Main::getInstance()->getDataFolder() . $locate . "/" . $stuffName . ".json")));
        $player->sendSkin();
    }

    /**
     * @throws JsonException
     */
    public static function setDefaultSkin(Player $player)
    {
        $skin = $player->getSkin();
        $name = $player->getXuid();
        $path = Main::getInstance()->getDataFolder() . "saveskin/" . $name . ".png";
        if(!file_exists($path)){
            return;
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
    //This is used to combine the player skin and the set skin
    public static function imgTricky(string $skinPath, string $stuffName, string $locate, array $size): string
    {
        $path = Main::getInstance()->getDataFolder();
        $down = imagecreatefrompng($skinPath);
        if ($size[0] * $size[1] * $size[2] == 65536) {
            $upper = self::resize_image($path . $locate . "/" . $stuffName . ".png", 128, 128);
        } else {
            $upper = self::resize_image($path . $locate . "/" . $stuffName . ".png", 64, 64);
        }
        //Remove black color out of the png
        imagecolortransparent($upper, imagecolorallocatealpha($upper, 0, 0, 0, 127));

        imagealphablending($down, true);
        imagesavealpha($down, true);
        imagecopymerge($down, $upper, 0, 0, 0, 0, $size[0], $size[1], 100);
        imagepng($down, $path . 'temp.png');
        return Main::getInstance()->getDataFolder() . 'temp.png';

    }

    public static function resize_image($file, $w, $h, $crop = FALSE): GdImage|bool
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

    public static function skinDataToImage($skinData): ?GdImage
    {
        $size = strlen($skinData);
        if (!self::validateSize($size)) {
            Main::getInstance()->getServer()->broadcastMessage("An error occur, id: 1");
            return null;
        }
        $width = self::$skin_widght_map[$size];
        $height = self::$skin_height_map[$size];
        $skinPos = 0;
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            Main::getInstance()->getServer()->broadcastMessage("An error occur,id: 2");
            return null;
        }

        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $r = ord($skinData[$skinPos]);
                $skinPos++;
                $g = ord($skinData[$skinPos]);
                $skinPos++;
                $b = ord($skinData[$skinPos]);
                $skinPos++;
                $a = 127 - intdiv(ord($skinData[$skinPos]), 2);
                $skinPos++;
                $col = imagecolorallocatealpha($image, $r, $g, $b, $a);
                imagesetpixel($image, $x, $y, $col);
            }
        }
        imagesavealpha($image, true);
        return $image;
    }

    public static function validateSize(int $size): bool
    {
        if (!in_array($size, self::$acceptedSkinSize)) {
            return false;
        }
        return true;
    }
}