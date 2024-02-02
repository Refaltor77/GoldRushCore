<?php

namespace core\blocks\alcool;

use core\blocks\crops\BerryBlack;
use core\blocks\crops\BerryBlue;
use core\blocks\crops\BerryPink;
use core\blocks\crops\BerryYellow;
use core\blocks\tiles\DistillerieTile;
use core\inventory\DistillerieInventory;
use core\items\foods\alcools\AlcoolPur;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\block\permutations\Permutable;
use customiesdevs\customies\block\permutations\RotatableTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Chest;
use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\SmokeParticle;

class Distillerie extends Transparent implements Permutable
{
    use RotatableTrait;

    public function ticksRandomly(): bool{return true;}

    public function readStateFromWorld(): self
    {
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20 * mt_rand(1, 3));
        return parent::readStateFromWorld();
    }

    public function onScheduledUpdate(): void
    {
        $this->onRandomTick();
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20 * mt_rand(1, 3));
    }


    public function onRandomTick(): void
    {
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if ($tile instanceof DistillerieTile) {
            $inventory = $tile->getInventory();

            $slotsBerries = DistillerieInventory::SLOT_BERRIES;

            if ($inventory->getItem(DistillerieInventory::SLOT_ALCOOL_PUR)::class !== AlcoolPur::class) {
                return;
            }


            $lastBerry = "";
            foreach ($slotsBerries as $slot) {
                $item = $inventory->getItem($slot);
                if ($lastBerry === "") {
                    $lastBerry = $item::class;
                } else {
                    if ($lastBerry !== $item::class) {
                        return;
                    }
                }
            }


            $itemResult = match ($lastBerry) {
                \core\items\crops\BerryBlue::class => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_SPEED),
                \core\items\crops\BerryYellow::class => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_HASTE),
                \core\items\crops\BerryBlack::class => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_FORCE),
                \core\items\crops\BerryPink::class => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_HEAL),
                default => "null"
            };

            if ($itemResult === "null") return;

            if ($inventory->getItem(DistillerieInventory::SLOT_RESULT)->isNull()) {

            } else {
                if ($inventory->getItem(DistillerieInventory::SLOT_RESULT)::class !== $itemResult::class) {
                    return;
                }
            }

            if ($inventory->getItem(DistillerieInventory::SLOT_RESULT)->getCount() >= 64) {
                return;
            }


            if ($tile->distille >= 15) {
                $this->launchParticle();
                $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
                $tile->distille = 0;
            } else {
                $this->launchParticle();
                $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
                $tile->distille++;
                return;
            }



            foreach ($slotsBerries as $slot) {
                $inventory->setItem($slot, $inventory->getItem($slot)->setCount($inventory->getItem($slot)->getCount() - 1));
            }

            $inventory->setItem(DistillerieInventory::SLOT_ALCOOL_PUR, $inventory->getItem(DistillerieInventory::SLOT_ALCOOL_PUR)->setCount($inventory->getItem(DistillerieInventory::SLOT_ALCOOL_PUR)->getCount() - 1));


            if ($inventory->getItem(DistillerieInventory::SLOT_RESULT)->isNull()) {
                $inventory->setItem(DistillerieInventory::SLOT_RESULT, $itemResult);
            } else {
                $inventory->setItem(DistillerieInventory::SLOT_RESULT, $inventory->getItem(DistillerieInventory::SLOT_RESULT)->setCount($inventory->getItem(DistillerieInventory::SLOT_RESULT)->getCount() + 1));
            }

        }
    }

    public function launchParticle(): void {
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.5, 0.3, 0.5), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 0.1, 0.6), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.6, 0.1, 0.5), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 0.2, 0.4), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.3, 0.2, 0.6), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 0.1, 0.5), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.5, 0.3, 0.4), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.6, 0.2, 0.6), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 0.5, 0.5), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.2, 0.1, 0.4), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.3, 0, 0.6), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 0, 0.4), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.5, 0, 0.5), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 0.1, 0.2), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.2, 0, 0.5), new FlameParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 0.3, 0.4), new FlameParticle());

        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.5, 1.0, 0.5), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 1.1, 0.4), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.3, 1.2, 0.3), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.2, 1.1, 0.2), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.1, 1.2, 0.1), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.2, 1.3, 0.1), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.3, 1.2, 0.1), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.4, 1.5, 0.1), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.5, 1.6, 0.1), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.1, 1.1, 0.2), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.1, 1.2, 0.3), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.1, 1.4, 0.4), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.1, 1.1, 0.5), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.6, 1.2, 0.2), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.7, 1.4, 0.5), new SmokeParticle());
        $this->getPosition()->getWorld()->addParticle($this->getPosition()->add(0.8, 1.3, 0.4), new SmokeParticle());
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
            if ($tile instanceof DistillerieTile) {
                $player->setCurrentWindow($tile->getInventory());
                return true;
            }
        }
        return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
    }
}