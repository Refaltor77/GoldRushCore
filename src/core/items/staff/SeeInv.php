<?php

namespace core\items\staff;

use core\api\gui\DoubleChestInventory;
use core\Main;
use core\player\CustomPlayer;
use core\traits\HomeTrait;
use core\traits\UtilsTrait;
use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SeeInv extends Item implements ItemComponents
{
    use ItemComponentsTrait;
    use UtilsTrait;
    use HomeTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'See Inventaire';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('stick', $inventory);
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Voir l'inventaire d'un joueur",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "SYLVANAR C MON GARRRRRRRS"
        ]);
    }

    public function onAttackEntity(Entity $victim, array &$returnedItems): bool
    {
        $damager = $victim->getLastDamageCause();
        if ($damager instanceof EntityDamageByEntityEvent) {
            $damager = $damager->getDamager();
            if ($damager instanceof CustomPlayer) {
                $this->onInteractEntity($damager, $victim, new Vector3(0, 0, 0));
            }
        }
        return false;
    }

    public function onInteractEntity(Player $player, Entity $entity, Vector3 $clickVector): bool
    {
        if (!Main::getInstance()->getStaffManager()->isInStaffMode($player) && !Server::getInstance()->isOp($player->getName())) return false;
        if ($entity instanceof CustomPlayer) {
            $itemsBarrier = VanillaBlocks::BARRIER()->asItem();
            $inv = new DoubleChestInventory();
            $inv->setName($entity->getName());
            if ($entity->hasFreeze()) {
                $i = 0;
                while ($i !== 36) {
                    $slotsContent[] = $i;
                    $i++;
                }
                $inv->setClickCallback(function (Player $player, Inventory $inventoryEvent, Item $target, Item $source, int $slot) use ($inv, $slotsContent, $entity): void {
                    if (in_array($slot, [45, 46, 48, 50, 52, 53, 36, 37, 38, 39, 40, 41, 42, 43, 44])) {
                        $inv->transacCancel();
                        return;
                    }
                    $content = [];
                    foreach ($slotsContent as $slotIndex) {
                        $content[] = $inv->getItem($slotIndex);
                    }
                    if ($entity->isConnected() && $entity->isAlive()) {
                        $entity->getInventory()->setContents($content);
                        $entity->getArmorInventory()->setHelmet($inv->getItem(47));
                        $entity->getArmorInventory()->setChestplate($inv->getItem(49));
                        $entity->getArmorInventory()->setLeggings($inv->getItem(51));
                        $entity->getArmorInventory()->setBoots($inv->getItem(53));
                    }
                });
            } else {
                $inv->setViewOnly();
            }
            $inv->setContents($entity->getInventory()->getContents());
            $slots = [45, 46, 48, 50, 52, 53, 36, 37, 38, 39, 40, 41, 42, 43, 44];
            foreach ($slots as $slot) $inv->setItem($slot, $itemsBarrier);
            $inv->setItem(47, $entity->getArmorInventory()->getHelmet());
            $inv->setItem(49, $entity->getArmorInventory()->getChestplate());
            $inv->setItem(51, $entity->getArmorInventory()->getLeggings());
            $inv->setItem(53, $entity->getArmorInventory()->getBoots());
            $inv->send($player);
        }
        return parent::onInteractEntity($player, $entity, $clickVector);
    }
}