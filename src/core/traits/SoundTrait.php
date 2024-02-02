<?php

namespace core\traits;

use pocketmine\player\Player;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpLevelUpSound;

trait SoundTrait
{
    public function sendSuccessSound(Player $player): void
    {
        $player->getWorld()->addSound($player->getEyePos(), new XpLevelUpSound(5), [$player]);
    }

    public function sendErrorSound(Player $player): void
    {
        $player->getWorld()->addSound($player->getEyePos(), new NoteSound(NoteInstrument::BASS_DRUM(), 7), [$player]);
    }

    public function sendPop(Player $player): void
    {
        $player->getWorld()->addSound($player->getEyePos(), new PopSound(), [$player]);
    }
}