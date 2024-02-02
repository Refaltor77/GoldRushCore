<?php

namespace core\inventory;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\SimpleInventory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;

class FakeBlockInventory extends SimpleInventory implements BlockInventory{
    use BlockInventoryTrait;

    protected string $title;

    protected int $defaultSize;
    protected int $windowType;

    protected ?Player $owner;
    protected Block $block;

    /** @var null|callable */
    private $packetCallable;

    public function __construct(Position $holder, int $size = 27, int|Block $block = BlockTypeIds::CHEST, int $windowType = WindowTypes::CONTAINER, callable $packetCallable = null, ?Player $owner = null){
        parent::__construct($size);
        $holder->x = intval($holder->x);
        $holder->y = intval($holder->y);
        $holder->z = intval($holder->z);
        if(is_int($block)){
            $block = VanillaBlocks::CHEST();
        }
        $this->block = $block;
        $this->defaultSize = $size;
        $this->windowType = $windowType;
        $this->packetCallable = $packetCallable;
        $this->owner = $owner;
        $this->holder = $holder;
    }

    public function getOwner(): ?Player{
        return $this->owner;
    }

    public function getNetworkType(): int{
        return $this->windowType;
    }

    public function getDefaultSize(): int{
        return $this->defaultSize;
    }

    public function getName(): string{
        return "Inventory";
    }

    public function setTitle(string $title): void{
        $this->title = $title;
    }

    public function getTitle(): string{
        return $this->title;
    }

    public function onOpen(Player $who): void{
        if($this->block->getTypeId() !== BlockTypeIds::AIR){
            $block = clone $this->block;
            $this->sendBlock($who, $this->holder, $block);
        }
        $who->getNetworkSession()->sendDataPacket(ContainerOpenPacket::blockInv($who->getNetworkSession()->getInvManager()->getCurrentWindowId(), $this->windowType, BlockPosition::fromVector3($this->holder)));
        $who->getNetworkSession()->getInvManager()->syncContents($this);
        parent::onOpen($who);
    }

    public function onClose(Player $who): void{
        if($this->block->getTypeId() !== BlockTypeIds::AIR){
            $block = $who->getWorld()->getBlock($this->holder);
            $this->sendBlock($who, $this->holder, $block);
        }
        parent::onClose($who);
    }

    public function setPacketCallable(?callable $packetCallable): void{
        $this->packetCallable = $packetCallable;
    }

    /**
     * @param Player $player
     * @param ServerboundPacket $packet
     * @return bool, false will cancel event, true will let event continue
     */
    public function handlePacket(Player $player, ServerboundPacket $packet): bool{
        $callable = $this->packetCallable;

        if($callable !== null){
            $callable($player, $packet);
        }
        return true;
    }

    public function sendBlock(Player $player, Vector3 $pos, Block $block): void{



        $blockTranslator = TypeConverter::getInstance()->getBlockTranslator();

        $packet = UpdateBlockPacket::create(
            BlockPosition::fromVector3($this->holder),
            $blockTranslator->internalIdToNetworkId($block->getStateId()),
            UpdateBlockPacket::FLAG_NETWORK,
            UpdateBlockPacket::DATA_LAYER_NORMAL
        );

        $player->getNetworkSession()->sendDataPacket($packet);
    }
}