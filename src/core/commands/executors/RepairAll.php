<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\traits\SoundTrait;
use core\traits\UtilsTrait;
use pocketmine\item\Durable;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilUseSound;

class RepairAll extends Executor
{
    use SoundTrait;
    use UtilsTrait;

    public static array $hasRepair = [];

    public function __construct(string $name = 'repairall', string $description = "Réparer vos items.", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('repair.all.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        $rank = Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid());

        $cooldown = 0;
        switch ($rank) {
            case "COWBOY":
                $cooldown = 60 * 4;
                break;
            case "MARSHALL":
                $cooldown = 60 * 2;
                break;
        }



        if (isset(self::$hasRepair[$sender->getXuid()]) && self::$hasRepair[$sender->getXuid()] > time()) {
            $timeCooldown = self::$hasRepair[$sender->getXuid()] - time();
            $msgTime = $this->calculTime($timeCooldown);
            $sender->sendMessage(Messages::message("§cIl vous reste " . $msgTime));
            return;
        }

        foreach ($sender->getInventory()->getContents() as $slot => $hand) {
            if ($hand instanceof Durable) {
                $sender->getInventory()->setItem($slot, $hand->setDamage(0));
            }
        }

        foreach ($sender->getArmorInventory() as $slot => $hand) {
            if ($hand instanceof Durable) {
                $sender->getInventory()->setItem($slot, $hand->setDamage(0));
            }
        }

        $sender->sendMessage(Messages::message("§eRepair all effectué !"));
        $sender->getWorld()->addSound($sender->getEyePos(), new AnvilUseSound());
        self::$hasRepair[$sender->getXuid()] = time() + $cooldown;
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