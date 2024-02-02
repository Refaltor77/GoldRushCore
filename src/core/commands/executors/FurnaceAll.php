<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\messages\Messages;
use core\traits\SoundTrait;
use pocketmine\crafting\FurnaceType;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class FurnaceAll extends Executor
{
    use SoundTrait;

    public static array $cooldown = [];

    public function __construct(string $name = 'furnaceall', string $description = "Cuit tout vos items dans votre inventaire.", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('furnace.all.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        $hand = $sender->getInventory()->getItemInHand();
        $furnaceManager = Server::getInstance()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::FURNACE());

        $found = false;
        foreach ($sender->getInventory()->getContents() as $slot => $item) {
            if ($furnaceManager->match($item) !== null) {
                $found = true;
                break;
            }
        }

        if ($found) {
            $rank = $this->getPlugin()->getRankManager()->getSupremeRankPriority($sender->getXuid());
            switch ($rank) {
                case "SHERIF":
                    foreach ($sender->getInventory()->getContents() as $slot => $item) {
                        $match = $furnaceManager->match($item);
                        if (!is_null($match)) {
                            $result = $match->getResult()->setCount($item->getCount());
                            $sender->getInventory()->setItem($slot, $result);
                        }
                    }
                    $sender->sendNotification("§fTous vos items ont été cuits.");
                    $this->sendSuccessSound($sender);
                    break;
                case "MARSHALL":
                    if (isset(self::$cooldown[$sender->getXuid()])) {
                        if (self::$cooldown[$sender->getXuid()] <= time()) {
                            self::$cooldown[$sender->getXuid()] = time() + 60 * 2;
                            foreach ($sender->getInventory()->getContents() as $slot => $item) {
                                $match = $furnaceManager->match($item);
                                if (!is_null($match)) {
                                    $result = $match->getResult()->setCount($item->getCount());
                                    $sender->getInventory()->setItem($slot, $result);
                                }
                            }
                            $sender->sendNotification("§fTous vos items ont été cuits.");
                            $this->sendSuccessSound($sender);
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendNotification("§cVous avez un cooldown de §4" . self::$cooldown[$sender->getXuid()] - time() . " seconde§c(§6s§c).");
                        }
                    } else {
                        self::$cooldown[$sender->getXuid()] = time() + 60 * 2;
                        foreach ($sender->getInventory()->getContents() as $slot => $item) {
                            $match = $furnaceManager->match($item);
                            if (!is_null($match)) {
                                $result = $match->getResult()->setCount($item->getCount());
                                $sender->getInventory()->setItem($slot, $result);
                            }
                        }
                        $sender->sendNotification("§fTous vos items ont été cuits.");
                        $this->sendSuccessSound($sender);
                    }
                    break;
                case "COWBOY":
            }

        } else {
            $this->sendErrorSound($sender);
            $sender->sendNotification("§cAucun de vos items ne peut être cuit.");
        }
    }
}