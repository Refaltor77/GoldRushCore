<?php

namespace core\items\cosmetics\head;

use core\settings\Ids;
use customiesdevs\customies\item\component\ArmorComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\component\WearableComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class PurpleBob extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public static string $nameString = '';

    public function __construct(ItemIdentifier $identifier)
    {
        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_ITEMS,
            CreativeInventoryInfo::CATEGORY_ITEMS,
        );


        parent::__construct($identifier, self::$nameString = "§6- §fBob " . TextFormat::LIGHT_PURPLE . "Violet §6-");

        $this->initComponent('cosmetic_head', $inventory);

        $this->addComponent(new ArmorComponent(5, ArmorComponent::TEXTURE_TYPE_ELYTRA));
        $this->addComponent(new WearableComponent(WearableComponent::SLOT_WEAPON_OFF_HAND, 5));
        $this->addComponent(new MaxStackSizeComponent(1));
        $this->allowOffHand();


        $this->allowOffHand();
    }

    public static function getStringId(): string
    {
        return Ids::PURPLE_BOB;
    }

}