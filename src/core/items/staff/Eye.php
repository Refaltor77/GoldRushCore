<?php

namespace core\items\staff;

use core\api\camera\CameraSystem;
use core\api\camera\EaseTypes;
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

class Eye extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Eye';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('eye', $inventory);
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Rentre dans la tête des gens",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "STARFOULA NAH LE SHEITAN"
        ]);
    }

    public function onInteractEntity(Player $player, Entity $entity, Vector3 $clickVector): bool
    {
        if (!Main::getInstance()->getStaffManager()->isInStaffMode($player) && !Server::getInstance()->isOp($player->getName())) return false;
        if ($entity instanceof CustomPlayer) {
            $camera = new CameraSystem($player);
            $player->sendMessage(Messages::message("§fPour sortir de la vue du joueur §l" . $entity->getName() . "§r§f faite §lsneak.§r"));
            $camera->createTiming(function (CameraSystem $camera, int $seconds, Player $player) use ($entity): void {
                if ($entity->isConnected()) {
                    if ($player->getWorld()->getFolderName() !== $entity->getWorld()->getFolderName()) {
                        $player->sendMessage(Messages::message("§cLe joueur vient de changer de monde !"));
                        $camera->stopTiming();
                        $player->showPlayer($entity);
                        return;
                    }

                    if ($player->isSneaking()) {
                        $camera->stopTiming();
                        $player->showPlayer($entity);
                        return;
                    }

                    $player->hidePlayer($entity);
                    $camera->setCameraPosition($entity->getEyePos(), EaseTypes::LINEAR, 0.5, $entity->getTargetBlock(10)?->getPosition()->asVector3());
                } else $camera->stopTiming();
            }, 5);
        }
        return parent::onInteractEntity($player, $entity, $clickVector);
    }

    public function onAttackEntity(Entity $entity, array &$returnedItems): bool
    {
        $damager = $entity->getLastDamageCause();

        if ($damager instanceof EntityDamageByEntityEvent) {
            $player = $damager->getDamager();
            if ($entity instanceof CustomPlayer && $player instanceof CustomPlayer) {
                $camera = new CameraSystem($player);
                $player->sendMessage(Messages::message("§fPour sortir de la vue du joueur §l" . $entity->getName() . "§r§f faite §lsneak.§r"));
                $camera->createTiming(function (CameraSystem $camera, int $seconds, Player $player) use ($entity): void {
                    if ($entity->isConnected()) {
                        if ($player->getWorld()->getFolderName() !== $entity->getWorld()->getFolderName()) {
                            $player->sendMessage(Messages::message("§cLe joueur vient de changer de monde !"));
                            $camera->stopTiming();
                            $player->showPlayer($entity);
                            return;
                        }

                        if ($player->isSneaking()) {
                            $camera->stopTiming();
                            $player->showPlayer($entity);
                            return;
                        }

                        $player->hidePlayer($entity);
                        $camera->setCameraPosition($entity->getEyePos(), EaseTypes::LINEAR, 0.1, $entity->getTargetBlock(10)?->getPosition()->asVector3());
                    } else $camera->stopTiming();
                }, 5);
            }
        }

        return true;
    }
}