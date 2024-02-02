<?php

namespace core\items\armors\others;

use customiesdevs\customies\item\component\ArmorComponent;
use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\WearableComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class HoodHelmet extends Armor implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Cagoule';


        $info = new ArmorTypeInfo(
            $this->getDefensePoints(),
            $this->getMaxDurability(),
            ArmorInventory::SLOT_HEAD
        );
        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_HELMET,
        );

        parent::__construct($identifier, $name, $info);

        $this->initComponent('cagoule', $inventory);

        $this->addComponent(new ArmorComponent($this->getDefensePoints(), "diamond"));
        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new WearableComponent(WearableComponent::SLOT_ARMOR_HEAD, $this->getDefensePoints()));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f La cagoule permet de cacher votre pseudo",
            "§6---",
            "§eDefense: §f" . $this->getDefensePoints() . " ",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }


    public function getDefensePoints(): int
    {
        return 1;
    }

    public function getMaxDurability(): int
    {
        return 1000;
    }
}