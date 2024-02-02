<?php

namespace core\items\tools\spectral;

use core\blocks\crops\ObsidianCrops;
use core\items\backpacks\BackpackFarm;
use core\items\crops\SeedsObsidian;
use core\Main;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\utils\Utils;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Crops;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Axe;
use pocketmine\item\Beetroot;
use pocketmine\item\Hoe;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ItemUseOnBlockSound;

class SpectralSword extends Axe implements ItemComponents
{
    use ItemComponentsTrait;


    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Épée spectral";

        $info = ToolTier::NETHERITE();

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_SWORD,
        );

        parent::__construct($identifier, $name, $info);

        $this->initComponent('spectral_sword', $inventory);

        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new MaxStackSizeComponent(1));
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'épée spectral,\noutils fiable et solide",
            "§6---",
            "§eAttack: §f" . $this->getAttackPoints() . " ",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);
    }

    public function getAttackPoints(): int
    {
        return VanillaItems::DIAMOND_SWORD()->getAttackPoints() + 6;
    }

    public function getMaxDurability(): int
    {
        return 10000;
    }
}