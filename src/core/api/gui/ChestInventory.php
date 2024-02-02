<?php

namespace core\api\gui;

use core\Main;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\SimpleInventory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;

class ChestInventory extends SimpleInventory implements BlockInventory
{

    use BlockInventoryTrait;

    public static array $hasSend = [];
    public $main;
    protected $name = "Coffre";
    protected $viewOnly = false;
    protected $clickCallback = null;
    protected $closeCallback = null;
    private bool $transacCancel = false;

    public function __construct(int $size = 27)
    {
        $this->main = Main::getInstance();
        parent::__construct($size);
    }

    public function transacCancel(): void
    {
        $this->transacCancel = true;
    }

    public function isCancelTransac(): bool
    {
        return $this->transacCancel;
    }

    public function reloadTransac(): void
    {
        $this->transacCancel = false;
    }

    public function isViewOnly(): bool
    {
        return $this->viewOnly;
    }

    public function setViewOnly(bool $value = true)
    {
        $this->viewOnly = $value;
    }

    public function getClickCallback()
    {
        return $this->clickCallback;
    }

    public function setClickCallback(?callable $callable)
    {
        $this->clickCallback = $callable;
    }

    public function onClose(Player $player): void
    {

        if (isset(self::$hasSend[$player->getXuid()])) {
            unset(self::$hasSend[$player->getXuid()]);
        }
        parent::onClose($player);
        // Real block

        $blockTranslator = TypeConverter::getInstance()->getBlockTranslator();

        $packet = UpdateBlockPacket::create(
            BlockPosition::fromVector3($this->holder),
            $blockTranslator->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId()),
            UpdateBlockPacket::FLAG_NETWORK,
            UpdateBlockPacket::DATA_LAYER_NORMAL
        );
        $player->getNetworkSession()->sendDataPacket($packet);
        $closeCallback = $this->getCloseCallback();
        if ($closeCallback !== null) {
            $closeCallback($player, $this);
        }
    }

    public function getCloseCallback()
    {
        return $this->closeCallback;
    }

    public function setCloseCallback(?callable $callable)
    {
        $this->closeCallback = $callable;
    }

    public function send(Player $player)
    {
        if (!isset(self::$hasSend[$player->getXuid()])) {


            // Set holder
            $this->holder = new Position((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY() + 3, (int)$player->getPosition()->getZ(), $player->getWorld());

            // Fake block

            $blockTranslator = TypeConverter::getInstance()->getBlockTranslator();
            $packet = UpdateBlockPacket::create(
                BlockPosition::fromVector3($this->holder),
                $blockTranslator->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId()),
                UpdateBlockPacket::FLAG_NETWORK,
                UpdateBlockPacket::DATA_LAYER_NORMAL
            );
            $player->getNetworkSession()->sendDataPacket($packet);

            // Fake tile
            $nbt = new CompoundTag();
            $nbt->setString(Nameable::TAG_CUSTOM_NAME, $this->getName());

            //
            $packet = BlockActorDataPacket::create(
                BlockPosition::fromVector3($this->holder),
                new CacheableNbt($nbt)
            );
            $player->getNetworkSession()->sendDataPacket($packet);

            // Set current window
            $player->setCurrentWindow($this);
            self::$hasSend[$player->getXuid()] = true;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $value)
    {
        $this->name = $value;
    }
}