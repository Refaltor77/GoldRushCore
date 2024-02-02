<?php

namespace core\entities\horse;

use core\listeners\types\horse\HorseEvent;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeFactory;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class HorseCopper extends Horse
{

    public static function getNetworkTypeId(): string
    {
        return "goldrush:horse_copper";
    }
}