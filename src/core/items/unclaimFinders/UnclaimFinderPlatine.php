<?php

namespace core\items\unclaimFinders;

use core\blocks\tiles\FlowerPercentTile;
use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\tile\MobHead;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Axe;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ItemBreakSound;

class UnclaimFinderPlatine extends Axe implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Unclaim finder en platine';

        $info = ToolTier::NETHERITE();

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_AXE,
        );

        parent::__construct($identifier, $name, $info);

        $this->initComponent('platinum_unclaim', $inventory);

        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'unclaim finder en platine permet de\nrechercher des coffres dans un 5 chunks.",
            "§6---",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§eDistance: 5 chunks§f",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);
    }

    public function getMaxDurability(): int
    {
        return 2000;
    }

    public function getAttackPoints(): int
    {
        return 1;
    }

    public function getMiningEfficiency(bool $isCorrectTool): float
    {
        return 1.0;
    }

    public function useItem(Player $player)
    {
        if ($this->getDamage() + 1 >= $this->getMaxDurability()) {
            $this->applyDamage(1);
        } else {
            $this->applyDamage(1);
            $i = 0;
            if ($player->getWorld()->getChunk($player->getPosition()->getFloorX() >> 4, $player->getPosition()->getFloorZ() >> 4) !== null) {
                foreach ($player->getWorld()->getChunk($player->getPosition()->getFloorX() >> 4, $player->getPosition()->getFloorZ() >> 4)?->getTiles() as $tile) {
                    if ($tile instanceof MobHead) {

                    } else {
                        if ($tile instanceof FlowerPercentTile) {
                            if ($i > 1) $i--;
                        } else $i++;
                    }
                }
            }

            if ($player->getWorld()->getChunk(($player->getPosition()->getFloorX() >> 4) + 1, $player->getPosition()->getFloorZ() >> 4) !== null) {
                foreach ($player->getWorld()->getChunk(($player->getPosition()->getFloorX() >> 4) + 1, $player->getPosition()->getFloorZ() >> 4)?->getTiles() as $tile) {
                    if ($tile instanceof MobHead) {

                    } else {
                        if ($tile instanceof FlowerPercentTile) {
                            if ($i > 1) $i--;
                        } else $i++;
                    }
                }
            }

            if ($player->getWorld()->getChunk(($player->getPosition()->getFloorX() >> 4) - 1, $player->getPosition()->getFloorZ() >> 4) !== null) {
                foreach ($player->getWorld()->getChunk(($player->getPosition()->getFloorX() >> 4) - 1, $player->getPosition()->getFloorZ() >> 4)?->getTiles() as $tile) {
                    if ($tile instanceof MobHead) {

                    } else {
                        if ($tile instanceof FlowerPercentTile) {
                            if ($i > 1) $i--;
                        } else $i++;
                    }
                }
            }

            if ($player->getWorld()->getChunk(($player->getPosition()->getFloorX() >> 4), ($player->getPosition()->getFloorZ() >> 4) + 1) !== null) {
                foreach ($player->getWorld()->getChunk($player->getPosition()->getFloorX() >> 4, ($player->getPosition()->getFloorZ() >> 4) + 1)?->getTiles() as $tile) {
                    if ($tile instanceof MobHead) {

                    } else {
                        if ($tile instanceof FlowerPercentTile) {
                            if ($i > 1) $i--;
                        } else $i++;
                    }
                }
            }


            $player->sendPopup("§e----------\n§7§oFiabilité: §64 chunks\n§7§oPourcentage: §e$i" . "%%\n§e----------");
            $player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $this);
        }
    }

    protected function getBaseMiningEfficiency(): float
    {
        return 1;
    }
}