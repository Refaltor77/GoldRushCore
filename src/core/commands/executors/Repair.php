<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\traits\SoundTrait;
use core\traits\UtilsTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Durable;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilUseSound;

class Repair extends Executor
{
    use SoundTrait;
    use UtilsTrait;


    public function __construct(string $name = 'repair', string $description = "Réparer votre item.", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('repair.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {

        if ($sender->getInventory()->getItemInHand()->getTypeId() === VanillaBlocks::AIR()->asItem()->getTypeId()) {
            $sender->sendMessage(Messages::message("§cVous ne pouvez pas réparer de l'air."));
            $this->sendErrorSound($sender);
            return;
        }

        if (!$sender->getInventory()->getItemInHand() instanceof Durable) {
            $sender->sendMessage(Messages::message("§cSeuls les items avec une durabilité ont réparable."));
            $this->sendErrorSound($sender);
            return;
        }


        $hand = $sender->getInventory()->getItemInHand();
        if ($hand instanceof Durable) {
            if ($hand->getStateId() === 0) {
                $sender->sendMessage(Messages::message("§cVotre item est déjà en bon état."));
                $this->sendErrorSound($sender);
                return;
            }
            $sender->getInventory()->setItemInHand($hand->setDamage(0));
            $sender->getWorld()->addSound($sender->getEyePos(), new AnvilUseSound());
        } else {
            $this->sendErrorSound($sender);
            $sender->sendMessage(Messages::message("§cVotre item ne se répare pas."));
        }
    }


    public function calculTime(int $int): string
    {
        $day = floor($int / 86400);
        $hourSec = $int % 86400;
        $hour = floor($hourSec / 3600);
        $minuteSec = $hourSec % 3600;
        $minute = floor($minuteSec / 60);
        $remainingSec = $minuteSec % 60;
        $second = ceil($remainingSec);
        if (!isset($day)) $day = 0;
        if (!isset($hour)) $hour = 0;
        if (!isset($minute)) $minute = 0;
        if (!isset($second)) $second = 0;

        if ($day >= 1) return $day . " jour§6(§fs§6)\n";
        if ($hour >= 1) return $hour . " heure§6(§fs§6)\n";
        if ($minute >= 1) return $minute . " minute§6(§fs§6)\n";
        if ($second >= 1) return $second . " seconde§6(§fs§6)\n";
        return "404";
    }
}