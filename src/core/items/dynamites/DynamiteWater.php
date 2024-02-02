<?php

namespace core\items\dynamites;

use core\items\ui\Chest;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\item\ProjectileItem;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ThrowSound;

class DynamiteWater extends ProjectileItem implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Dynamite anti-liquide";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );


        parent::__construct($identifier, $name);

        $this->initComponent('water_dynamite', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Dynamite anti liquide - spécialement conçue\npour une utilisation sous des fluides,",
            "§r§felle inspire la terreur à vos ennemis et peut\ndétruire les obstacles les plus robustes.",
            "§6---",
            "§l§eUtilité: §r§fPermet de retirer l'eau et la lave.§r",
            "§l§eForce: §l2",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);

    }


    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $block = $player->getTargetBlock(5);
        if ($block instanceof Chest) return ItemUseResult::FAIL();
        $location = $player->getLocation();

        $projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
        $projectile->setMotion($directionVector->multiply($this->getThrowForce()));

        $projectileEv = new ProjectileLaunchEvent($projectile);
        $projectileEv->call();
        if ($projectileEv->isCancelled()) {
            $projectile->flagForDespawn();
            return ItemUseResult::FAIL();
        }

        $projectile->spawnToAll();

        $location->getWorld()->addSound($location, new ThrowSound());

        $this->pop();

        return ItemUseResult::SUCCESS();
    }

    public function getThrowForce(): float
    {
        return 1.1;
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new \core\entities\dynamites\WaterDynamite($location, $thrower);
    }
}