<?php

namespace core\sounds;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\world\sound\Sound;

class BoneMealUseSound implements Sound
{
    public function encode(Vector3 $vector3) : array {
        return [LevelEventPacket::create(
            eventId: LevelEvent::BONE_MEAL_USE,
            eventData: 0,
            position: $vector3
        )];
    }
}