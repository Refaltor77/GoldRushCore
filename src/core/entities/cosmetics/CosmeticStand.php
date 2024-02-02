<?php

namespace core\entities\cosmetics;

use core\inventory\OffHandInventoryCustom;
use core\managers\ranks\RankManager;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;

class CosmeticStand extends Living
{
    private Item $itemCosmetic;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $this->setNameTagAlwaysVisible(true);
        $location->pitch = 0.0;
        $location->yaw = round($location->getYaw() / 90) * 90;
        $location->x = $location->getFloorX() + 0.5;
        $location->y = $location->getFloorY();
        $location->z = $location->getFloorZ() + 0.5;
        if ($nbt !== null) {
            if ($nbt->getString('item_cosmetic', 'none') !== 'none') {
                $this->itemCosmetic = Utils::unserializeItem($nbt->getString('item_cosmetic'));
            } else $this->itemCosmetic = VanillaItems::AIR();
        } else $this->itemCosmetic = VanillaItems::AIR();

        parent::__construct($location, $nbt);
    }


    protected function tryChangeMovement(): void
    {

    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    public function onNearbyBlockChange(): void
    {

    }

    public function saveNBT(): CompoundTag
    {
        $nbt =  parent::saveNBT();
        $nbt->setString('item_cosmetic', Utils::serilizeItem(($this->itemCosmetic->isNull() ? VanillaItems::STICK() : $this->itemCosmetic)));
        return $nbt;
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->getArmorInventory()->setItem(0, $this->itemCosmetic);
        $this->setNameTagAlwaysVisible(true);
    }

    public function attack(EntityDamageEvent $source): void
    {
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof CustomPlayer) {
                if (Server::getInstance()->isOp($damager->getName())) {
                    $item = $damager->getInventory()->getItemInHand();
                    $this->getArmorInventory()->setItem(0, $item);
                    $this->setNameTag($item->getName());
                    $this->setNameTagVisible(true);
                    $this->itemCosmetic = $item;

                    if ($item->isNull()) {
                        $this->setNameTag("");
                        $this->setNameTagVisible(false);
                    }
                }
            }
        }
    }


    public static function setCosmeticStandForPlayer(Item $item, Player $player, string $type = 'hat'): void {
        switch ($type) {
            case 'hat':
                $location = new Location(26, 62, 11,$player->getWorld(),  90, 90);
                break;
            case 'back':
                $location = new Location(26, 62, 11,$player->getWorld(),  0, 0);
                break;
        }


        $entity = new self($location);
        $entity->setNameTag("");
        $entity->setNameTagVisible(false);
        $entity->getArmorInventory()->setItem(0, $item);
        $entity->spawnTo($player);
        Utils::timeout(function () use ($entity) : void {
            if (!$entity->isFlaggedForDespawn()) $entity->flagForDespawn();
        }, 20 * 10);
        $entity->setNameTag($item->getName());
    }


    public static function getNetworkTypeId(): string
    {
        return 'goldrush:cosmetic_stand';
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2, 0.5, 1);
    }

    public function getName(): string
    {
        return 'Cosmetic Stand';
    }
}