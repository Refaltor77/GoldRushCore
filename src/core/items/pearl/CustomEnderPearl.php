<?php

namespace core\items\pearl;

use customiesdevs\customies\item\component\CooldownComponent;
use customiesdevs\customies\item\component\ThrowableComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl as EnderPearlEntity;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ProjectileItem;
use pocketmine\player\Player;

class CustomEnderPearl extends ProjectileItem implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Ender Perle";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('ender_pearl_custom', $inventory);
        $this->addComponent(new ThrowableComponent(true));
        $this->addComponent(new CooldownComponent("attack", $this->getCooldownTicks() / 20));
    }

    public function getCooldownTicks(): int
    {
        return 20 * 30;
    }

    public function getMaxStackSize(): int
    {
        return 16;
    }

    public function getThrowForce(): float
    {
        return 1.5;
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new EnderPearlEntity($location, $thrower);
    }
}