<?php

namespace core\cooldown;

use pocketmine\player\Player;

class BasicCooldown
{
    public static array $cooldownChest = [];
    public static array $cooldownSylvanar = [];
    public static array $cooldownSouls = [];

    public static function validChest(Player $player): bool {
        if (!isset(self::$cooldownChest[$player->getXuid()])) {
            self::$cooldownChest[$player->getXuid()] = time() + 1;
            return true;
        } else {
            if (self::$cooldownChest[$player->getXuid()] > time()) {
                return false;
            } else {
                self::$cooldownChest[$player->getXuid()] = time() + 1;
                return true;
            }
        }
    }


    public static function validCustom(Player $player, int $cooldown): bool {
        if (!isset(self::$cooldownChest[$player->getXuid()])) {
            self::$cooldownChest[$player->getXuid()] = time() + $cooldown;
            return true;
        } else {
            if (self::$cooldownChest[$player->getXuid()] > time()) {
                return false;
            } else {
                self::$cooldownChest[$player->getXuid()] = time() + $cooldown;
                return true;
            }
        }
    }



    public static function validSylvanar(Player $player): bool {
        if (!isset(self::$cooldownSylvanar[$player->getXuid()])) {
            self::$cooldownSylvanar[$player->getXuid()] = time() + 60 * 60 * 24;
            return true;
        } else {
            if (self::$cooldownSylvanar[$player->getXuid()] > time()) {
                return false;
            } else {
                self::$cooldownSylvanar[$player->getXuid()] = time() + 60 * 60 * 24;
                return true;
            }
        }
    }


    public static function validSouls(Player $player): bool {
        if (!isset(self::$cooldownChest[$player->getXuid()])) {
            self::$cooldownSouls[$player->getXuid()] = time() + 60 * 60 * 24;
            return true;
        } else {
            if (self::$cooldownSouls[$player->getXuid()] > time()) {
                return false;
            } else {
                self::$cooldownSouls[$player->getXuid()] = time() + 60 * 60 * 24;
                return true;
            }
        }
    }


    public static function removeCooldown(Player $player): void {
        if (isset(self::$cooldownChest[$player->getXuid()])) {
            unset(self::$cooldownChest[$player->getXuid()]);
        }
    }
}