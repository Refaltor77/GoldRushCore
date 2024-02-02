<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\inventory\CustomCraftingTableInventory;
use core\Main;
use pocketmine\block\inventory\CraftingTableInventory;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;

class Craft extends Executor
{
    public function __construct(string $name = 'craft', string $description = "Ouvrir une table de craft", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('craft.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        $blockTranslator = TypeConverter::getInstance()->getBlockTranslator();
        $vector = $sender->getPosition()->add(0, 2, 0);
        $pk = new UpdateBlockPacket();
        $pk->flags = UpdateBlockPacket::FLAG_NOGRAPHIC;
        $pk->blockPosition = BlockPosition::fromVector3($vector->add(0, 1, 0));
        $pk->blockRuntimeId = $blockTranslator->internalIdToNetworkId(VanillaBlocks::CRAFTING_TABLE()->getStateId());
        $sender->getNetworkSession()->sendDataPacket($pk);
        $inv = new CraftingTableInventory(new Position($vector->getX(), $vector->getY() + 1, $vector->getZ(), $sender->getWorld()));
        $sender->setCurrentWindow($inv);
        Main::$inCraftingTableCommand[$sender->getName()] = true;
    }
}