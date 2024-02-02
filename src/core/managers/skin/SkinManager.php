<?php

namespace core\managers\skin;

use core\Main;
use core\managers\Manager;
use pocketmine\entity\Skin;
use pocketmine\player\Player;

class SkinManager extends Manager
{

    const ACCEPTED_SKIN_SIZE = [
        64 * 32 * 4,
        64 * 64 * 4,
        128 * 128 * 4
    ];

    const SKIN_WIDTH_MAP = [
        64 * 32 * 4 => 64,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128
    ];

    const SKIN_HEIGHT_MAP = [
        64 * 32 * 4 => 32,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128
    ];

    private function getPath(): string
    {
        return Main::getInstance()->getDataFolder() . "skins/players/";
    }

    public function saveSkin(Player $player): void
    {
        $skin = $player->getSkin();
        $img = $this->skinDataToImage($skin->getSkinData());
        if (is_null($skin)) return;
        imagepng($img, $this->getPath() . strtolower($player->getName()) . ".png");
    }

    private function skinDataToImage($skinData)
    {
        $size = strlen($skinData);
        if (!$this->validateSize((int)$size)) {
            Main::getInstance()->getLogger()->error("Une erreur est survenue lors de la sauvegarde d'un skin");
            return null;
        }
        $width = self::SKIN_WIDTH_MAP[$size];
        $height = self::SKIN_HEIGHT_MAP[$size];
        $skinPos = 0;
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            Main::getInstance()->getLogger()->error("Une erreur est survenue lors de la sauvegarde d'un skin");
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

    private function validateSize(int $size): bool
    {
        if (!in_array($size, self::ACCEPTED_SKIN_SIZE)) {
            return false;
        }
        return true;
    }

    public function getSkin(Player|string $player): string
    {
        if ($player instanceof Player) {
            $name = strtolower($player->getName());
        } else {
            $name = strtolower($player);
        }
        return $this->getPath() . $name . ".png";
    }

    public function skinIsSave(Player|string $player): bool
    {
        if ($player instanceof Player) {
            $name = $player->getName();
        } else {
            $name = strtolower($player);
        }
        return file_exists($this->getPath() . strtolower($name) . ".png");
    }

    public function resetSkin(Player $player): void
    {
        $skin = $player->getSkin();
        $data = $this->getSkinData($this->getSkin($player));
        $newSkin = new Skin($skin->getSkinId(), $data);
        $player->setSkin($newSkin);
    }

    private function getSkinData(string $imagePath): string
    {
        $img = imagecreatefrompng($imagePath);
        $size = getimagesize($imagePath);
        $bytes = "";
        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $x < $size[0]; $x++) {
                $color = imagecolorat($img, $x, $y);
                $bytes .= $this->getStr($color, $bytes);
            }
        }
        @imagedestroy($img);
        return $bytes;
    }

    public function getStr(bool|int $colorat, string $bytes): string
    {
        $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
        $r = ($colorat >> 16) & 0xff;
        $g = ($colorat >> 8) & 0xff;
        $b = $colorat & 0xff;
        $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
        return $bytes;
    }

    /**
     * @throws \JsonException
     */
    public function setSkin(Player $sender, string $name): void
    {
        $skinData = $this->getSkinData($this->getSkin($name));
        $skin = $sender->getSkin();
        $newSkin = new Skin($skin->getSkinId(), $skinData,"", "geometry.humanoid.custom");
        $sender->setSkin($newSkin);
        $sender->sendSkin();
    }
}