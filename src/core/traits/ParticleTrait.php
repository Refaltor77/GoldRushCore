<?php

namespace core\traits;

use pocketmine\player\Player;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpLevelUpSound;

trait ParticleTrait
{
    public function sendVillagerHappyParticle(Player $player, ?Position $position = null): void {
        if (is_null($position)) $position = $player->getPosition();


        for ($i = 0; $i < 10; $i++) {
            $position->getWorld()->addParticle($position, new HappyVillagerParticle());
        }
    }


    public function sendExplodeParticle(Player $player, ?Position $position = null): void {
        if (is_null($position)) $position = $player->getPosition();


        for ($i = 0; $i < 10; $i++) {
            $position->getWorld()->addParticle($position, new ExplodeParticle());
            $position->getWorld()->addSound($position, new ExplodeSound());
        }
    }
}