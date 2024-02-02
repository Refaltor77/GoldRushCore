<?php

namespace core\commands\executors;

use core\api\form\ModalForm;
use core\api\gui\ChestInventory;
use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\inventory\EcInventoryCustom;
use core\items\backpacks\BackpackFarm;
use core\items\backpacks\BackpackOre;
use core\Main;
use core\managers\enderChest\EnderChestSlot;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\block\inventory\EnderChestInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;

class Ec extends Executor
{
    use SoundTrait;


    public function __construct(string $name = 'ec', string $description = "Voir sont ender chest", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('ec.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $inv = new EcInventoryCustom(who: $sender);
        $sender->setCurrentWindow($inv);
    }
}