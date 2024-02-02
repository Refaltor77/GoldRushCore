<?php

namespace core\listeners\types\entity;

use core\blocks\blocks\chest\AmethystChest;
use core\blocks\blocks\chest\EmeraldChest;
use core\blocks\blocks\chest\PlatinumChest;
use core\blocks\blocks\obsidian\ObsidianAmethyst;
use core\blocks\blocks\obsidian\ObsidianBasic;
use core\blocks\blocks\obsidian\ObsidianEmerald;
use core\blocks\blocks\obsidian\ObsidianPlatinum;
use core\listeners\BaseEvent;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\entity\EntityExplodeEvent;

class OnEntityExplode extends BaseEvent
{
    public function onEntityExplode(EntityExplodeEvent $event): void {
        foreach ($event->getBlockList() as $block) {
            if ($block instanceof ObsidianEmerald ||
                $block instanceof ObsidianPlatinum ||
                $block instanceof ObsidianAmethyst ||
                $block instanceof EmeraldChest ||
                $block instanceof AmethystChest ||
                $block instanceof PlatinumChest ||
                $block->getTypeId() === BlockTypeIds::OBSIDIAN
            ) {
                $blockList = $event->getBlockList();
                unset($blockList[array_search($block, $blockList)]);
                $event->setBlockList($blockList);
                $block->onExplode($event->getEntity());
            }
        }
    }
}