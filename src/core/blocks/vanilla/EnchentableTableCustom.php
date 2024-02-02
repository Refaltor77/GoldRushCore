<?php

namespace core\blocks\vanilla;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Slider;
use core\api\form\MenuForm;
use core\inventory\EnchantInventory;
use core\items\tools\lumberjack\AbstractWoodenAxe;
use corepvp\items\tools\copper\CopperHammer;
use corepvp\items\tools\emerald\EmeraldHammer;
use corepvp\items\tools\gold\GoldHammer;
use corepvp\items\tools\platinum\PlatinumHammer;
use corepvp\items\tools\amethyst\AmethystHammer;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\block\EnchantingTable;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\lang\Translatable;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class EnchentableTableCustom extends EnchantingTable
{
    use SoundTrait;

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        $hammerClass = [
            CopperHammer::class,
            EmeraldHammer::class,
            AmethystHammer::class,
            PlatinumHammer::class,
            AmethystHammer::class,
            GoldHammer::class
        ];


        if ($player instanceof CustomPlayer) {
            $itemInHand = $player->getInventory()->getItemInHand();
            $slot = $player->getInventory()->getHeldItemIndex();

            if ($itemInHand instanceof AbstractWoodenAxe) {
                $player->sendMessage(Messages::message("§cUne hache de bûcheron n'est pas compatible avec les enchantements."));
                $this->sendErrorSound($player);
                return false;
            }

            if (in_array($itemInHand::class, $hammerClass)) {
                $player->sendForm(new MenuForm("§6- §fEnchant §6-", "§7Bienvenue sur le formulaire pour enchanter votre Hammer.", [
                    new Button('§e- §fSolidité §e-'),
                ], function (Player $player, Button $button) use ($slot, $itemInHand) : void {
                    $player->sendForm(new CustomForm("§e- §fSolidité §e-", [
                        new Slider("Niveau", 1, 3, 1, 1),
                    ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                        if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                            $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                            return;
                        }


                        $slider = $response->getSlider();
                        $int = $slider->getValue();
                        $xp = $player->getXpManager()->getXpLevel();
                        $cout = 10 * $int;
                        if ($cout > $xp) {
                            $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                            return;
                        }


                        if ($itemInHand->getEnchantmentLevel(VanillaEnchantments::UNBREAKING()) > 0) {
                            $lvl = $itemInHand->getEnchantmentLevel(VanillaEnchantments::UNBREAKING());
                            if ($lvl === 3) {
                                $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                return;
                            }


                            if ($lvl >= $int) {
                                $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                return;
                            }
                        }

                        if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                            $itemInHand->removeEnchantment(VanillaEnchantments::UNBREAKING());
                            $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), $int));
                            $this->sendSuccessSound($player);
                            $player->getXpManager()->setXpLevel($xp - $cout);
                            $player->getInventory()->setItemInHand($itemInHand);
                        }else{
                            $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                        }
                    }));
                }));
            } elseif ($itemInHand instanceof Pickaxe || $itemInHand instanceof Axe || $itemInHand instanceof Shovel) {
                $player->sendForm(new MenuForm("§6- §fEnchant §6-", "§7Bienvenue sur le formulaire pour enchanter votre Pioche.", [
                    new Button('§e- §fEfficacité §e-'),
                    new Button('§e- §fSolidité §e-'),
                ], function (Player $player, Button $button) use ($slot, $itemInHand) : void {


                    switch ($button->getValue()) {
                        case 0:
                            $player->sendForm(new CustomForm("§e- §fEfficacité §e-", [
                                new Slider("Niveau", 1, 5, 1, 1),
                            ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                                if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                                    $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                                    return;
                                }


                                $slider = $response->getSlider();
                                $int = $slider->getValue();
                                $xp = $player->getXpManager()->getXpLevel();
                                $cout = 10 * $int;
                                if ($cout > $xp) {
                                    $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                                    return;
                                }


                                if ($itemInHand->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY()) > 0) {
                                    $lvl = $itemInHand->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY());
                                    if ($lvl === 5) {
                                        $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                        return;
                                    }


                                    if ($lvl >= $int) {
                                        $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                        return;
                                    }
                                }

                                if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                                    $itemInHand->removeEnchantment(VanillaEnchantments::EFFICIENCY());
                                    $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), $int));
                                    $this->sendSuccessSound($player);
                                    $player->getXpManager()->setXpLevel($xp - $cout);
                                    $player->getInventory()->setItemInHand($itemInHand);
                                }else{
                                    $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                                }
                            }));
                            break;
                        case 1:
                            $player->sendForm(new CustomForm("§e- §fSolidité §e-", [
                                new Slider("Niveau", 1, 3, 1, 1),
                            ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                                if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                                    $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                                    return;
                                }


                                $slider = $response->getSlider();
                                $int = $slider->getValue();
                                $xp = $player->getXpManager()->getXpLevel();
                                $cout = 10 * $int;
                                if ($cout > $xp) {
                                    $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                                    return;
                                }


                                if ($itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING)) > 0) {
                                    $lvl = $itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING));
                                    if ($lvl === 3) {
                                        $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                        return;
                                    }


                                    if ($lvl >= $int) {
                                        $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                        return;
                                    }
                                }

                                if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                                    $itemInHand->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING));
                                    $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING), $int));
                                    $this->sendSuccessSound($player);
                                    $player->getXpManager()->setXpLevel($xp - $cout);
                                    $player->getInventory()->setItemInHand($itemInHand);
                                }else{
                                    $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                                }
                            }));
                            break;
                    }
                }));
            } elseif ($itemInHand instanceof Hoe) {
                $player->sendForm(new MenuForm("§6- §fEnchant §6-", "§7Bienvenue sur le formulaire pour enchanter votre Houe.", [
                    new Button('§e- §fSolidité §e-'),
                ], function (Player $player, Button $button) use ($slot, $itemInHand) : void {
                    $player->sendForm(new CustomForm("§e- §fSolidité §e-", [
                        new Slider("Niveau", 1, 3, 1, 1),
                    ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                        if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                            $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                            return;
                        }


                        $slider = $response->getSlider();
                        $int = $slider->getValue();
                        $xp = $player->getXpManager()->getXpLevel();
                        $cout = 10 * $int;
                        if ($cout > $xp) {
                            $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                            return;
                        }


                        if ($itemInHand->getEnchantmentLevel(VanillaEnchantments::UNBREAKING()) > 0) {
                            $lvl = $itemInHand->getEnchantmentLevel(VanillaEnchantments::UNBREAKING());
                            if ($lvl === 3) {
                                $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                return;
                            }


                            if ($lvl >= $int) {
                                $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                return;
                            }
                        }

                        if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                            $itemInHand->removeEnchantment(VanillaEnchantments::UNBREAKING());
                            $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), $int));
                            $this->sendSuccessSound($player);
                            $player->getXpManager()->setXpLevel($xp - $cout);
                            $player->getInventory()->setItemInHand($itemInHand);
                        }else{
                            $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                        }
                    }));
                }));
            } elseif ($itemInHand instanceof Sword) {
                $player->sendForm(new MenuForm("§6- §fEnchant §6-", "§7Bienvenue sur le formulaire pour enchanter votre Item.", [
                    new Button('§e- §fSolidité §e-'),
                    new Button('§e- §fTranchant §e-'),
                    new Button('§e- §fAura de feu §e-'),
                   // new Button('§e- §fChâtiment §e-'), // TODO: add chatiment enchant
                ], function (Player $player, Button $button) use ($slot, $itemInHand) : void {
                    switch ($button->getValue()) {
                        case 0:
                            $player->sendForm(new CustomForm("§e- §fSolidité §e-", [
                                new Slider("Niveau", 1, 3, 1, 1),
                            ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                                if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                                    $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                                    return;
                                }


                                $slider = $response->getSlider();
                                $int = $slider->getValue();
                                $xp = $player->getXpManager()->getXpLevel();
                                $cout = 10 * $int;
                                if ($cout > $xp) {
                                    $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                                    return;
                                }


                                if ($itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING)) > 0) {
                                    $lvl = $itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING));
                                    if ($lvl === 3) {
                                        $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                        return;
                                    }


                                    if ($lvl >= $int) {
                                        $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                        return;
                                    }
                                }

                                if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                                    $itemInHand->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING));
                                    $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING), $int));
                                    $this->sendSuccessSound($player);
                                    $player->getXpManager()->setXpLevel($xp - $cout);
                                    $player->getInventory()->setItemInHand($itemInHand);
                                }else{
                                    $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                                }
                            }));
                            break;
                        case 1:
                            $player->sendForm(new CustomForm("§e- §fTranchant §e-", [
                                new Slider("Niveau", 1, 5, 1, 1),
                            ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                                if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                                    $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                                    return;
                                }


                                $slider = $response->getSlider();
                                $int = $slider->getValue();
                                $xp = $player->getXpManager()->getXpLevel();
                                $cout = 10 * $int;
                                if ($cout > $xp) {
                                    $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                                    return;
                                }


                                if ($itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SHARPNESS)) > 0) {
                                    $lvl = $itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SHARPNESS));
                                    if ($lvl === 5) {
                                        $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                        return;
                                    }


                                    if ($lvl >= $int) {
                                        $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                        return;
                                    }
                                }

                                if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                                    $itemInHand->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SHARPNESS));
                                    $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SHARPNESS), $int));
                                    $this->sendSuccessSound($player);
                                    $player->getXpManager()->setXpLevel($xp - $cout);
                                    $player->getInventory()->setItemInHand($itemInHand);
                                }else{
                                    $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                                }
                            }));
                            break;
                        case 2:
                            $player->sendForm(new CustomForm("§e- §fAura de feu §e-", [
                                new Slider("Niveau", 1, 2, 1, 1),
                            ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                                if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                                    $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                                    return;
                                }


                                $slider = $response->getSlider();
                                $int = $slider->getValue();
                                $xp = $player->getXpManager()->getXpLevel();
                                $cout = 10 * $int;
                                if ($cout > $xp) {
                                    $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                                    return;
                                }


                                if ($itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::FIRE_ASPECT)) > 0) {
                                    $lvl = $itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::FIRE_ASPECT));
                                    if ($lvl === 2) {
                                        $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                        return;
                                    }


                                    if ($lvl >= $int) {
                                        $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                        return;
                                    }
                                }

                                if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                                    $itemInHand->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::FIRE_ASPECT));
                                    $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::FIRE_ASPECT), $int));
                                    $this->sendSuccessSound($player);
                                    $player->getXpManager()->setXpLevel($xp - $cout);
                                    $player->getInventory()->setItemInHand($itemInHand);
                                }else{
                                    $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                                }
                            }));
                            break;
                        case 3:
                            $player->sendForm(new CustomForm("§e- §fChâtiment §e-", [
                                new Slider("Niveau", 1, 5, 1, 1),
                            ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                                if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                                    $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                                    return;
                                }


                                $slider = $response->getSlider();
                                $int = $slider->getValue();
                                $xp = $player->getXpManager()->getXpLevel();
                                $cout = 10 * $int;
                                if ($cout > $xp) {
                                    $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                                    return;
                                }


                                if ($itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SMITE)) > 0) {
                                    $lvl = $itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SMITE));
                                    if ($lvl === 5) {
                                        $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                        return;
                                    }


                                    if ($lvl >= $int) {
                                        $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                        return;
                                    }
                                }

                                if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                                    $itemInHand->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SMITE));
                                    $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SMITE), $int));
                                    $this->sendSuccessSound($player);
                                    $player->getXpManager()->setXpLevel($xp - $cout);
                                    $player->getInventory()->setItemInHand($itemInHand);
                                }else{
                                    $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                                }
                            }));
                            break;
                    }
                }));
            } elseif ($itemInHand instanceof Armor) {
                $player->sendForm(new MenuForm("§6- §fEnchant §6-", "§7Bienvenue sur le formulaire pour enchanter votre Item.", [
                    new Button('§e- §fSolidité §e-'),
                    new Button('§e- §fProtection §e-'),
                ], function (Player $player, Button $button) use ($slot, $itemInHand) : void {
                    switch ($button->getValue()) {
                        case 0:
                            $player->sendForm(new CustomForm("§e- §fSolidité §e-", [
                                new Slider("Niveau", 1, 3, 1, 1),
                            ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                                if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                                    $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                                    return;
                                }


                                $slider = $response->getSlider();
                                $int = $slider->getValue();
                                $xp = $player->getXpManager()->getXpLevel();
                                $cout = 10 * $int;
                                if ($cout > $xp) {
                                    $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                                    return;
                                }


                                if ($itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING)) > 0) {
                                    $lvl = $itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING));
                                    if ($lvl === 3) {
                                        $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                        return;
                                    }


                                    if ($lvl >= $int) {
                                        $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                        return;
                                    }
                                }

                                if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                                    $itemInHand->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING));
                                    $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING), $int));
                                    $this->sendSuccessSound($player);
                                    $player->getXpManager()->setXpLevel($xp - $cout);
                                    $player->getInventory()->setItemInHand($itemInHand);
                                }else{
                                    $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                                }
                            }));
                            break;
                        case 1:
                            $player->sendForm(new CustomForm("§e- §fProtection §e-", [
                                new Slider("Niveau", 1, 4, 1, 1),
                            ], function (Player $player, CustomFormResponse $response) use ($itemInHand, $slot): void {
                                if ($slot !== $player->getInventory()->getHeldItemIndex()) {
                                    $player->sendMessage(Messages::message("§cUne erreur est survenue sur le slot de votre item."));
                                    return;
                                }


                                $slider = $response->getSlider();
                                $int = $slider->getValue();
                                $xp = $player->getXpManager()->getXpLevel();
                                $cout = 10 * $int;
                                if ($cout > $xp) {
                                    $player->sendMessage(Messages::message("§cIl vous faut §4" . $cout . " §cd'xp pour enchanter votre outil"));
                                    return;
                                }


                                if ($itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::PROTECTION)) > 0) {
                                    $lvl = $itemInHand->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::PROTECTION));
                                    if ($lvl === 4) {
                                        $player->sendMessage(Messages::message("§cVotre item est déjà enchanté au niveau maximum."));
                                        return;
                                    }


                                    if ($lvl >= $int) {
                                        $player->sendMessage(Messages::message("§cVotre item ne peut pas être enchanté à un niveau inférieur."));
                                        return;
                                    }
                                }

                                if($player->getInventory()->getItemInHand()::class === $itemInHand::class) {
                                    $itemInHand->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::PROTECTION));
                                    $itemInHand = $itemInHand->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::PROTECTION), $int));
                                    $this->sendSuccessSound($player);
                                    $player->getXpManager()->setXpLevel($xp - $cout);
                                    $player->getInventory()->setItemInHand($itemInHand);
                                }else{
                                    $player->sendMessage("§4Vous ne pouvez pas enchanter de l'air.");
                                }
                            }));
                            break;
                    }
                }));
            }
        }

        return true;
    }
}