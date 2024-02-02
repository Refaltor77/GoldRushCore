<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\ExperienceBottle;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class XpBottle extends Executor
{
    use SoundTrait;

    public static array $cooldown = [];

    public function __construct(string $name = "xpbottle", string $description = "Convertissez votre expérience en bouteilles d'XP.", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("xpbottle.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $rank = Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid());


        switch ($rank) {
            case "FARMER":
            case "BRAQUEUR":
            case "BANDIT":
                if (isset(self::$cooldown[$sender->getXuid()])) {
                    if (self::$cooldown[$sender->getXuid()] <= time()) {
                        // code
                        if ($sender->getXpManager()->getCurrentTotalXp() > 0) {
                            $xp = $sender->getXpManager()->getCurrentTotalXp();
                            $totalBottle = floor($xp / 10);
                            $item = CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP);
                            $item->setCustomName("Xp: " . $xp)->getNamedTag()->setString("total_xp", $xp);
                            if ($sender->getInventory()->canAddItem($item)) {
                                $sender->getInventory()->addItem($item);
                                $sender->getXpManager()->setCurrentTotalXp(0);
                                self::$cooldown[$sender->getXuid()] = time() + 60 * 30;
                            } else {
                                $this->sendErrorSound($sender);
                                $sender->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                            }

                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage(Messages::message("§cVous n'avez pas d'expérience à convertir."));
                        }
                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendMessage(Messages::message("§cVous avez un cooldown de §4" . self::$cooldown[$sender->getXuid()] - time() . " seconde§c(§4s§c)."));
                    }
                } else {
                    if ($sender->getXpManager()->getCurrentTotalXp() > 0) {
                        $xp = $sender->getXpManager()->getCurrentTotalXp();
                        $totalBottle = floor($xp / 10);
                        $item = CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP);
                        $item->setCustomName("Xp: " . $xp)->getNamedTag()->setString("total_xp", $xp);
                        if ($sender->getInventory()->canAddItem($item)) {
                            $sender->getInventory()->addItem($item);
                            $sender->getXpManager()->setCurrentTotalXp(0);
                            self::$cooldown[$sender->getXuid()] = time() + 60 * 30;
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                        }

                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendMessage(Messages::message("§cVous n'avez pas d'expérience à convertir."));
                    }
                }
                break;
            case "COWBOY":
                if (isset(self::$cooldown[$sender->getXuid()])) {
                    if (self::$cooldown[$sender->getXuid()] <= time()) {
                        // code
                        if ($sender->getXpManager()->getCurrentTotalXp() > 0) {
                            $xp = $sender->getXpManager()->getCurrentTotalXp();
                            $totalBottle = floor($xp / 10);
                            $item = CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP);
                            $item->setCustomName("Xp: " . $xp)->getNamedTag()->setString("total_xp", $xp);
                            if ($sender->getInventory()->canAddItem($item)) {
                                $sender->getInventory()->addItem($item);
                                $sender->getXpManager()->setCurrentTotalXp(0);
                                self::$cooldown[$sender->getXuid()] = time() + 60 * 15;
                            } else {
                                $this->sendErrorSound($sender);
                                $sender->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                            }

                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage(Messages::message("§cVous n'avez pas d'expérience à convertir."));
                        }
                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendMessage(Messages::message("§cVous avez un cooldown de §4" . self::$cooldown[$sender->getXuid()] - time() . " seconde§c(§4s§c)."));
                    }
                } else {
                    if ($sender->getXpManager()->getCurrentTotalXp() > 0) {
                        $xp = $sender->getXpManager()->getCurrentTotalXp();
                        $totalBottle = floor($xp / 10);
                        $item = CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP);
                        $item->setCustomName("Xp: " . $xp)->getNamedTag()->setString("total_xp", $xp);
                        if ($sender->getInventory()->canAddItem($item)) {
                            $sender->getInventory()->addItem($item);
                            $sender->getXpManager()->setCurrentTotalXp(0);
                            self::$cooldown[$sender->getXuid()] = time() + 60 * 15;
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                        }

                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendMessage(Messages::message("§cVous n'avez pas d'expérience à convertir."));
                    }
                }
                break;
            case "MARSHALL":
                if (isset(self::$cooldown[$sender->getXuid()])) {
                    if (self::$cooldown[$sender->getXuid()] <= time()) {
                        // code
                        if ($sender->getXpManager()->getCurrentTotalXp() > 0) {
                            $xp = $sender->getXpManager()->getCurrentTotalXp();
                            $totalBottle = floor($xp / 10);
                            $item = CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP);
                            $item->setCustomName("Xp: " . $xp)->getNamedTag()->setString("total_xp", $xp);
                            if ($sender->getInventory()->canAddItem($item)) {
                                $sender->getInventory()->addItem($item);
                                $sender->getXpManager()->setCurrentTotalXp(0);
                                self::$cooldown[$sender->getXuid()] = time() + 60 * 5;
                            } else {
                                $this->sendErrorSound($sender);
                                $sender->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                            }

                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage(Messages::message("§cVous n'avez pas d'expérience à convertir."));
                        }
                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendMessage(Messages::message("§cVous avez un cooldown de §4" . self::$cooldown[$sender->getXuid()] - time() . " seconde§c(§4s§c)."));
                    }
                } else {
                    if ($sender->getXpManager()->getCurrentTotalXp() > 0) {
                        $xp = $sender->getXpManager()->getCurrentTotalXp();
                        $totalBottle = floor($xp / 10);
                        $item = CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP);
                        $item->setCustomName("Xp: " . $xp)->getNamedTag()->setString("total_xp", $xp);
                        if ($sender->getInventory()->canAddItem($item)) {
                            $sender->getInventory()->addItem($item);
                            $sender->getXpManager()->setCurrentTotalXp(0);
                            self::$cooldown[$sender->getXuid()] = time() + 60 * 5;
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                        }

                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendMessage(Messages::message("§cVous n'avez pas d'expérience à convertir."));
                    }
                }
                break;
            case "SHERIF":
                if ($sender->getXpManager()->getCurrentTotalXp() > 0) {
                    $xp = $sender->getXpManager()->getCurrentTotalXp();
                    $totalBottle = floor($xp / 10);
                    $item = CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP);
                    $item->setCustomName("Xp: " . $xp)->getNamedTag()->setString("total_xp", $xp);
                    if ($sender->getInventory()->canAddItem($item)) {
                        $sender->getInventory()->addItem($item);
                        $sender->getXpManager()->setCurrentTotalXp(0);
                        self::$cooldown[$sender->getXuid()] = time() + 60 * 5;
                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                    }

                } else {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage(Messages::message("§cVous n'avez pas d'expérience à convertir."));
                }
                break;
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}