<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Kits extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'kit', string $description = "Prendre un kit", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $rank = Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid());
        switch ($rank) {
            default:
            case "PLAYER":
                if (isset($args[0])) {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage(Messages::message("§cEn tant que joueur, vous n'avez accès qu'au grade de base, c'est-à-dire 'joueur'. Il vous suffit simplement de taper /kit pour obtenir votre kit joueur. Si vous souhaitez avoir accès à davantage de kits, veuillez acheter un grade sur la boutique : https://shop.goldrushmc.fun"));
                    return;
                }
                $this->getPlugin()->getKitManager()->getCooldownKit($sender, "player",
                    function (Player $player, int $cooldown): void {
                    if ($cooldown <= time()) {
                        $content = [
                            VanillaItems::IRON_HELMET()->setCustomName("§l§eKit joueur"),
                            VanillaItems::IRON_CHESTPLATE()->setCustomName("§l§eKit joueur"),
                            VanillaItems::IRON_LEGGINGS()->setCustomName("§l§eKit joueur"),
                            VanillaItems::IRON_BOOTS()->setCustomName("§l§eKit joueur"),
                            VanillaItems::DIAMOND_PICKAXE()->setCustomName("§l§eKit joueur"),
                            VanillaItems::DIAMOND_PICKAXE()->setCustomName("§l§eKit joueur"),
                            VanillaItems::STEAK()->setCount(64)->setCustomName("§l§eKit joueur"),
                            VanillaBlocks::TORCH()->asItem()->setCount(64)->setCustomName("§l§eKit joueur"),
                        ];

                        $canAdd = true;
                        foreach ($content as $item) {
                            if (!$player->getInventory()->canAddItem($item)) {
                                $canAdd = false;
                            }
                        }

                        if (!$canAdd) {
                            Main::getInstance()->sendErrorSound($player);
                            $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                            return;
                        } else {
                            foreach ($content as $item) {
                                $player->getInventory()->addItem($item);
                            }
                            Main::getInstance()->getKitManager()->setCooldownKit($player, "player");
                        }
                    } else {
                        Main::getInstance()->sendErrorSound($player);
                        $player->sendMessage(Messages::message("§cIl vous reste " . Kits::calculTime($cooldown - time()) . " §cavant de pouvoir utiliser votre kit."));
                    }
                });
                break;
            case "BANDIT":
                if (!isset($args[0]) || !in_array($args[0], ['pvp', 'farm'])) {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage(Messages::message("§c/kit <pvp:farm>"));
                    return;
                }
                $this->getPlugin()->getKitManager()->getCooldownKit($sender, "bandit",
                    function (Player $player, int $cooldown) use ($args) : void {
                        if ($cooldown <= time()) {

                            switch ($args[0]) {
                                case 'pvp':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HELMET)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_CHESTPLATE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_LEGGINGS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_BOOTS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SWORD)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2)),
                                    ];
                                    break;
                                case 'farm':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_AXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SHOVEL)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_PICKAXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HOE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                    ];
                                    break;
                            }


                            $canAdd = true;
                            foreach ($content as $item) {
                                if (!$player->getInventory()->canAddItem($item)) {
                                    $canAdd = false;
                                }
                            }

                            if (!$canAdd) {
                                Main::getInstance()->sendErrorSound($player);
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                                return;
                            } else {
                                foreach ($content as $item) {
                                    $player->getInventory()->addItem($item);
                                }
                                Main::getInstance()->getKitManager()->setCooldownKit($player, "bandit");
                            }
                        } else {
                            Main::getInstance()->sendErrorSound($player);
                            $player->sendMessage(Messages::message("§cIl vous reste " . Kits::calculTime($cooldown - time()) . " §cavant de pouvoir utiliser votre kit."));
                        }
                    });
                break;
            case "BRAQUEUR":
                if (!isset($args[0]) || !in_array($args[0], ['pvp', 'farm'])) {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage(Messages::message("§c/kit <pvp:farm>"));
                    return;
                }
                $this->getPlugin()->getKitManager()->getCooldownKit($sender, "braqueur",
                    function (Player $player, int $cooldown) use ($args) : void {
                        if ($cooldown <= time()) {

                            switch ($args[0]) {
                                case 'pvp':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HELMET)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_CHESTPLATE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_LEGGINGS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_BOOTS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SWORD)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2)),
                                    ];
                                    break;
                                case 'farm':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_AXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SHOVEL)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_PICKAXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HOE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                    ];
                                    break;
                            }


                            $canAdd = true;
                            foreach ($content as $item) {
                                if (!$player->getInventory()->canAddItem($item)) {
                                    $canAdd = false;
                                }
                            }

                            if (!$canAdd) {
                                Main::getInstance()->sendErrorSound($player);
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                                return;
                            } else {
                                foreach ($content as $item) {
                                    $player->getInventory()->addItem($item);
                                }
                                Main::getInstance()->getKitManager()->setCooldownKit($player, "braqueur");
                            }
                        } else {
                            Main::getInstance()->sendErrorSound($player);
                            $player->sendMessage(Messages::message("§cIl vous reste " . Kits::calculTime($cooldown - time()) . " §cavant de pouvoir utiliser votre kit."));
                        }
                    });
                break;
            case "COWBOY":
                if (!isset($args[0]) || !in_array($args[0], ['pvp', 'farm'])) {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage(Messages::message("§c/kit <pvp:farm>"));
                    return;
                }
                $this->getPlugin()->getKitManager()->getCooldownKit($sender, "cowboy",
                    function (Player $player, int $cooldown) use ($args) : void {
                        if ($cooldown <= time()) {

                            switch ($args[0]) {
                                case 'pvp':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HELMET)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_LEGGINGS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_BOOTS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SWORD)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2)),
                                    ];
                                    break;
                                case 'farm':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_AXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SHOVEL)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_PICKAXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HOE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
                                    ];
                                    break;
                            }


                            $canAdd = true;
                            foreach ($content as $item) {
                                if (!$player->getInventory()->canAddItem($item)) {
                                    $canAdd = false;
                                }
                            }

                            if (!$canAdd) {
                                Main::getInstance()->sendErrorSound($player);
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                                return;
                            } else {
                                foreach ($content as $item) {
                                    $player->getInventory()->addItem($item);
                                }
                                Main::getInstance()->getKitManager()->setCooldownKit($player, "cowboy");
                            }
                        } else {
                            Main::getInstance()->sendErrorSound($player);
                            $player->sendMessage(Messages::message("§cIl vous reste " . Kits::calculTime($cooldown - time()) . " §cavant de pouvoir utiliser votre kit."));
                        }
                    });
                break;
            case "MARSHALL":
                if (!isset($args[0]) || !in_array($args[0], ['pvp', 'farm'])) {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage(Messages::message("§c/kit <pvp:farm>"));
                    return;
                }
                $this->getPlugin()->getKitManager()->getCooldownKit($sender, "marshall",
                    function (Player $player, int $cooldown) use ($args) : void {
                        if ($cooldown <= time()) {

                            switch ($args[0]) {
                                case 'pvp':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HELMET)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_LEGGINGS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_BOOTS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SWORD)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2)),
                                    ];
                                    break;
                                case 'farm':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_AXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SHOVEL)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_PICKAXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HOE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4)),
                                    ];
                                    break;
                            }


                            $canAdd = true;
                            foreach ($content as $item) {
                                if (!$player->getInventory()->canAddItem($item)) {
                                    $canAdd = false;
                                }
                            }

                            if (!$canAdd) {
                                Main::getInstance()->sendErrorSound($player);
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                                return;
                            } else {
                                foreach ($content as $item) {
                                    $player->getInventory()->addItem($item);
                                }
                                Main::getInstance()->getKitManager()->setCooldownKit($player, "marshall");
                            }
                        } else {
                            Main::getInstance()->sendErrorSound($player);
                            $player->sendMessage(Messages::message("§cIl vous reste " . Kits::calculTime($cooldown - time()) . " §cavant de pouvoir utiliser votre kit."));
                        }
                    });
                break;
            case "SHERIF":
                if (!isset($args[0]) || !in_array($args[0], ['pvp', 'farm'])) {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage(Messages::message("§c/kit <pvp:farm>"));
                    return;
                }
                $this->getPlugin()->getKitManager()->getCooldownKit($sender, "sherif",
                    function (Player $player, int $cooldown) use ($args) : void {
                        if ($cooldown <= time()) {

                            switch ($args[0]) {
                                case 'pvp':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HELMET)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_LEGGINGS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_BOOTS)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2)),
                                    ];
                                    break;
                                case 'farm':
                                    $content = [
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_AXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SHOVEL)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_PICKAXE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4)),
                                        CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HOE)
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4)),
                                    ];
                                    break;
                            }


                            $canAdd = true;
                            foreach ($content as $item) {
                                if (!$player->getInventory()->canAddItem($item)) {
                                    $canAdd = false;
                                }
                            }

                            if (!$canAdd) {
                                Main::getInstance()->sendErrorSound($player);
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                                return;
                            } else {
                                foreach ($content as $item) {
                                    $player->getInventory()->addItem($item);
                                }
                                Main::getInstance()->getKitManager()->setCooldownKit($player, "sherif");
                            }
                        } else {
                            Main::getInstance()->sendErrorSound($player);
                            $player->sendMessage(Messages::message("§cIl vous reste " . Kits::calculTime($cooldown - time()) . " §cavant de pouvoir utiliser votre kit."));
                        }
                    });
                break;
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, "pvp");
        $this->addSubCommand(1, "farm");

        $this->addComment(0, 1, "Kit pvp");
        $this->addComment(1, 1, "Kit farm");
        return parent::loadOptions($player);
    }


    public static function calculTime(int $int): string
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

        if ($day >= 1) return $day . " jour§4(§cs§4)\n";
        if ($hour >= 1) return $hour . " heure§4(§cs§4)\n";
        if ($minute >= 1) return $minute . " minute§4(§cs§4)\n";
        if ($second >= 1) return $second . " seconde§4(§cs§4)\n";
        return "404";
    }
}