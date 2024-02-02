<?php

namespace core\items\others;

use core\entities\CameraEntity;
use core\events\LogEvent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CameraItem extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, "Appareil photo");
        $this->initComponent('camera', $inventory);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'appareil photo permet de pouvoir vous filmer\nn'importe où et n'importe quand.",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
    {
        $t = $blockReplace->getPosition();
        $location = new Location($t->getX() + 0.5, $t->getY(), $t->getZ() + 0.5, $t->getWorld(), $player->getLocation()->getYaw(), $player->getLocation()->getPitch());
        $entity = new CameraEntity($location);
        $entity->spawnToAll();
        $pos = $player->getPosition();
        $x = $pos->getX();
        $y = $pos->getY();
        $z = $pos->getZ();
        $world = $pos->getWorld()->getFolderName();
        (new LogEvent($player->getName() . " a possé une camera au position ({$x},{$y},{$z},{$world})", "camera"))->call();
        $player->getInventory()->setItemInHand(VanillaItems::AIR());
        return ItemUseResult::SUCCESS();
    }
}