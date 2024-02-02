<?php

namespace core\items\staff;

use core\api\timings\TimingsSystem;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Freeze extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Freeze Stick';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('freeze', $inventory);
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Freeze des joueurs",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "TROP RARE WSH"
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
            if (Main::getInstance()->getStaffManager()->isInStaffMode($player)) {
                if ($entity->hasFreeze()) {
                    $entity->setFreeze(false);
                    $entity->sendMessage(Messages::message("§fVous pouvez désormais vous déplacer normalement."));
                    $entity->sendSuccessSound();
                } else {
                    $entity->setFreeze(true);
                    $entity->sendErrorSound();


                    $timing = new TimingsSystem();
                    $timing->createTiming(function (TimingsSystem $timingsSystem, int $second) use ($entity, $player): void {
                        if (!$entity->isConnected()) {
                            $timingsSystem->stopTiming();
                            return;
                        }
                        if ($entity->hasFreeze()) {
                            $entity->sendTitle("§cUn Modérateur vous a gelé !", "§4(§cDeconnexion §4= §cban automatique§4)", 0, 1);
                        } else $timingsSystem->stopTiming();
                    });
                }
            }
        }
        return parent::onInteractEntity($player, $entity, $clickVector);
    }
}