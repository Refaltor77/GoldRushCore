<?php

namespace core\tasks;

use core\player\CustomPlayer;
use core\settings\BlockIds;
use core\traits\HomeTrait;
use core\traits\UtilsTrait;
use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\BlockPunchParticle;
use pocketmine\world\sound\BlockBreakSound;

class BlockJumpTask extends Task
{
    public Block $block;
    public CustomPlayer $player;
    public int $ticks = 0;

    public static array $posCache = [];

    use UtilsTrait;

    public function __construct(Block $block, CustomPlayer $player)
    {
        $this->block = $block;
        $this->player = $player;
    }

    public function onRun(): void
    {
        $pos = $this->block->getPosition();
        if (!$this->player->isConnected()) {
            if (!$this->getHandler()->isCancelled()) {
                $this->getHandler()->cancel();
            }
            return;
        }

        self::$posCache[$this->positionToString($pos)] = true;

        $blockTranslator = TypeConverter::getInstance()->getBlockTranslator();
        if ($this->ticks === 60) {
            $packet = UpdateBlockPacket::create(
                BlockPosition::fromVector3($pos),
                $blockTranslator->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId()),
                UpdateBlockPacket::FLAG_NETWORK,
                UpdateBlockPacket::DATA_LAYER_NORMAL
            );
            $pos->getWorld()->addSound($pos, new BlockBreakSound(VanillaBlocks::STONE()));
            $pos->getWorld()->addParticle($pos, new BlockBreakParticle($this->block));
            $this->player->getNetworkSession()->sendDataPacket($packet);
        } elseif ($this->ticks === 100) {
            $packet = UpdateBlockPacket::create(
                BlockPosition::fromVector3($pos),
                $blockTranslator->internalIdToNetworkId(CustomiesBlockFactory::getInstance()->get(BlockIds::BLOCK_JUMP)->getStateId()),
                UpdateBlockPacket::FLAG_NETWORK,
                UpdateBlockPacket::DATA_LAYER_NORMAL
            );
            $this->player->getNetworkSession()->sendDataPacket($packet);
            if (!$this->getHandler()->isCancelled()) {
                unset(self::$posCache[$this->positionToStringPlayer($pos, $this->player)]);
                $this->getHandler()->cancel();
            }
        }
        $this->ticks++;
    }
}