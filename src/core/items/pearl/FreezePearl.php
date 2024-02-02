<?php

namespace core\items\pearl;

use core\entities\projectils\FreezePearlEntity;
use customiesdevs\customies\item\component\CooldownComponent;
use customiesdevs\customies\item\component\ThrowableComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ProjectileItem;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FreezePearl extends ProjectileItem implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Perle de freeze";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('freeze_pearl', $inventory);
        $this->addComponent(new ThrowableComponent(true));
        $this->addComponent(new CooldownComponent("attack", $this->getCooldownTicks() / 20));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une ender pearl refroidit dans\nles abysses du mont Calypsia.",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }

    public function getCooldownTicks(): int
    {
        return 20 * 10;
    }

    public function getThrowForce(): float
    {
        return 1.5;
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new FreezePearlEntity($location, $thrower);
    }
}