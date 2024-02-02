<?php

namespace core\commands\executors\secret;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\world\Position;

class Rtp extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'rtp-1375', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if ($sender->getInventory()->getItemInHand() instanceof \core\items\others\Rtp) {
            $x = mt_rand(2000, 3000);
            $z = mt_rand(2000, 3000);
            $y = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getHighestBlockAt($x, $z) + 1;
            $sender->teleport(new Position($x, $y, $z, Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()));
            $sender->sendMessage(Messages::message("§eTéléportation effectué !"));

            $sender->getInventory()->removeItem(CustomiesItemFactory::getInstance()->get(Ids::RTP));
            $this->sendSuccessSound($sender);
        } else {
            $sender->sendMessage("§r§6[§fRTP§6] §l§6»§r Tu ne possède pas de ticket de téléportation en main !");
            $this->sendErrorSound($sender);
        }
    }
}