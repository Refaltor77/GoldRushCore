<?php

namespace core\listeners\types\items;

use corepvp\items\tools\amethyst\AmethystHammer;
use corepvp\items\tools\copper\CopperHammer;
use corepvp\items\tools\emerald\EmeraldHammer;
use corepvp\items\tools\gold\GoldHammer;
use corepvp\items\tools\goldWhite\GoldWhiteHammer;
use corepvp\items\tools\platinum\PlatinumHammer;
use core\listeners\BaseEvent;
use core\Main;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\format\Chunk;
use pocketmine\world\particle\BlockBreakParticle;

class HammerBreak extends BaseEvent
{
    private array $cacheFace = [];

    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $this->cacheFace[$player->getXuid()] = $event->getFace();
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld();

        if (in_array(get_class($item), [
            AmethystHammer::class,
            CopperHammer::class,
            EmeraldHammer::class,
            GoldHammer::class,
            GoldWhiteHammer::class,
            PlatinumHammer::class
        ])) {
            $item = VanillaItems::DIAMOND_PICKAXE();
            if (isset($this->cacheFace[$player->getXuid()])) {
                $obsi = VanillaBlocks::OBSIDIAN()->getTypeId();
                $toolType = BlockToolType::PICKAXE;
                if ($this->cacheFace[$player->getXuid()] === 1 || $this->cacheFace[$player->getXuid()] === 0) {
                    for ($x = -1; $x < 2; $x++) {
                        if ($x === -1) {
                            $block = $pos->getWorld()->getBlock($pos->add($x, 0, abs($x)));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, 0, abs($x)), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add($x, 0, $x));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, 0, $x), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add($x, 0, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, 0, 0), $item, $player);
                                }
                            }
                        }

                        if ($x === 0) {
                            $block = $pos->getWorld()->getBlock($pos->add($x, 0, -1));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, 0, -1), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add($x, 0, 1));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, 0, 1), $item, $player);
                                }
                            }
                        }

                        if ($x === 1) {
                            $block = $pos->getWorld()->getBlock($pos->add($x, 0, $x));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, 0, $x), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add($x, 0, -$x));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, 0, -$x), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add($x, 0, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, 0, 0), $item, $player);
                                }
                            }
                        }
                    }
                } elseif ($this->cacheFace[$player->getXuid()] === 4 || $this->cacheFace[$player->getXuid()] === 5) {
                    for ($x = -1; $x < 2; $x++) {
                        if ($x === -1) {
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, abs($x)));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, abs($x)), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, $x));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, $x), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, 0), $item, $player);
                                }
                            }
                        }

                        if ($x === 0) {
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, $x - 1));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, $x - 1), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, $x + 1));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, $x + 1), $item, $player);
                                }
                            }
                        }

                        if ($x === 1) {
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, $x));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, $x), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, -$x));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, -$x), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, 0), $item, $player);
                                }
                            }
                        }
                    }
                } elseif ($this->cacheFace[$player->getXuid()] === 3 || $this->cacheFace[$player->getXuid()] == 2) {

                    for ($x = -1; $x < 2; $x++) {
                        if ($x === -1) {
                            $block = $pos->getWorld()->getBlock($pos->add(abs($x), $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(abs($x), $x, 0), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add($x, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, $x, 0), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, 0), $item, $player);
                                }
                            }
                        }

                        if ($x === 0) {
                            $block = $pos->getWorld()->getBlock($pos->add($x - 1, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x - 1, $x, 0), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add($x + 1, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x + 1, $x, 0), $item, $player);
                                }
                            }
                        }

                        if ($x === 1) {
                            $block = $pos->getWorld()->getBlock($pos->add($x, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add($x, $x, 0), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add(-$x, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(-$x, $x, 0), $item, $player);
                                }
                            }
                            $block = $pos->getWorld()->getBlock($pos->add(0, $x, 0));
                            if (!Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) || Main::getInstance()->getAreaManager()->isInArea($block->getPosition()) && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break'] === true) {
                                if ($block->getBreakInfo()->getToolType() === $toolType && $block->getTypeId() !== $obsi && $block->getTypeId() !== VanillaBlocks::BEDROCK()->getTypeId()) {
                                    $world->useBreakOn($pos->add(0, $x, 0), $item, $player);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}