<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\traits\SoundTrait;
use pocketmine\crafting\FurnaceType;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class Furnace extends Executor
{
    use SoundTrait;

    public static array $cooldown = [];

    public function __construct(string $name = 'furnace', string $description = "Cuit vos items dans la main", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('furnace.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        $hand = $sender->getInventory()->getItemInHand();
        $furnaceManager = Server::getInstance()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::FURNACE());
        $match = $furnaceManager->match($hand);
        if ($match !== null) {

            $rank = $this->getPlugin()->getRankManager()->getSupremeRankPriority($sender->getXuid());
            switch ($rank) {
                case "BANDIT":
                case "BRAQUEUR":
                    if (isset(self::$cooldown[$sender->getXuid()])) {
                        if (self::$cooldown[$sender->getXuid()] <= time()) {
                            self::$cooldown[$sender->getXuid()] = time() + 60 * 3;
                            $result = $match->getResult()->setCount($hand->getCount());
                            $sender->getInventory()->setItemInHand($result);
                            $this->sendSuccessSound($sender);
                            $sender->sendMessage(Messages::message("§fVotre objet vient d'être cuit !"));
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage(Messages::message("§cVous avez un cooldown de §4" . self::$cooldown[$sender->getXuid()] - time() . " seconde§c(§6s§c)."));
                        }
                    } else {
                        self::$cooldown[$sender->getXuid()] = time() + 60 * 3;
                        $result = $match->getResult()->setCount($hand->getCount());
                        $sender->getInventory()->setItemInHand($result);
                        $this->sendSuccessSound($sender);
                        $sender->sendMessage(Messages::message("§fVotre objet vient d'être cuit !"));
                    }
                    break;
                case "SHERIF":
                case "MARSHALL":
                case "COWBOY":
                    $result = $match->getResult()->setCount($hand->getCount());
                    $sender->getInventory()->setItemInHand($result);
                $this->sendSuccessSound($sender);
                $sender->sendMessage(Messages::message("§fVotre objet vient d'être cuit !"));
                    break;
            }

        } else {
            if (!in_array(Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid()), [
                "SHERIF", "MARSHALL", "COWBOY", "BRAQUEUR", "BANDIT"
            ])) {
                $sender->sendMessage(Messages::message("§cVous n'avez pas la permission !"));
                return;
            }
            $this->sendErrorSound($sender);
            $sender->sendMessage(Messages::message("§cVotre item ne se cuit pas."));
        }
    }
}