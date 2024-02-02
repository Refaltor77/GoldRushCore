<?php

namespace core\blocks\containers;

use core\blocks\tiles\BarrelTile;
use core\cooldown\BasicCooldown;
use core\items\crops\Raisin;
use core\items\foods\alcools\EmptyBottle;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\block\permutations\Permutable;
use customiesdevs\customies\block\permutations\RotatableTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\AnvilBreakSound;
use pocketmine\world\sound\WaterSplashSound;
use pocketmine\world\sound\XpCollectSound;

class Barrel extends Opaque implements Permutable
{

    use RotatableTrait;
    use SoundTrait;


    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            if (!BasicCooldown::validChest($player)) return false;
            if ($player->getInventory()->getItemInHand() instanceof EmptyBottle) {
                $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
                if ($tile instanceof BarrelTile) {
                    if ($tile->getInventory()->contains(CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI)->setCount(9))) {
                        if ($player->getInventory()->canAddItem(CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUR))) {
                            $tile->getInventory()->removeItem(CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI)->setCount(9));
                            $item = $player->getInventory()->getItemInHand();
                            $player->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
                            $player->getInventory()->addItem(CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUR));
                            $player->getWorld()->addSound($player->getPosition(), new WaterSplashSound(1), [$player]);
                            //$player->sendMessage(Messages::message("§fTu as fait de l'alcool pur ! Va à ta table de craft pour créer des alcools spéciaux avec des baies de couleurs"));
                        } else {
                            $this->sendErrorSound($player);
                            $player->sendMessage(Messages::message("§cTu n'as pas assez de place dans ton inventaire pour créer de l'alcool pur."));
                        }
                    } else {
                        $this->sendErrorSound($player);
                        $player->sendMessage(Messages::message("§cPour faire de l'alcool pur, place plus de 9 raisins mûrs dans le baril. Le raisin frais que tu ajoutes murira dedans."));
                    }
                }
            } else {
                $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
                if ($tile instanceof BarrelTile) {
                    $player->setCurrentWindow($tile->getInventory());
                    return true;
                }
            }
        }
        return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
    }

    public function readStateFromWorld(): Block
    {
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 60);
        return parent::readStateFromWorld();
    }


    public function onScheduledUpdate(): void
    {
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20 * 60);
        $this->onRandomTick();
    }

    public function onRandomTick(): void
    {
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if ($tile instanceof BarrelTile) {
            $inventory = $tile->getInventory();
            foreach ($inventory->getContents() as $slot => $item) {
                if ($item instanceof Raisin) {
                    if ($inventory->canAddItemMoisie(CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI))) {
                        $inventory->removeItem($item->setCount(1));
                        $inventory->addItemMoisie(CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI));
                        $this->getPosition()->getWorld()->addSound($this->getPosition(), new XpCollectSound());
                        $this->getPosition()->getWorld()->addParticle(new Position(
                            $this->getPosition()->getX() + 0.5,
                            $this->getPosition()->getZ() + 1,
                            $this->getPosition()->getZ() + 0.5,
                            $this->getPosition()->getWorld()
                        ), new BlockBreakParticle(VanillaBlocks::GLAZED_TERRACOTTA()->setColor(DyeColor::GREEN())));
                    }
                    break;
                }
            }
        }
    }

    public function ticksRandomly(): bool
    {
        return true;
    }
}