<?php

namespace core\items\staff;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\HomeTrait;
use core\traits\UtilsTrait;
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

class HomeManage extends Item implements ItemComponents
{
    use ItemComponentsTrait;
    use UtilsTrait;
    use HomeTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Home Manage';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('diamond', $inventory);
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Voir les homes du joueur",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "GOLDRUSH MEILLEUR QUE PALA ? (oui)"
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
            $homes = Main::getInstance()->getHomeManager()->getAllHomesPlayer($entity);
            $btn = [];

            $i = 1;
            foreach ($homes as $homeName => $posHash) {
                $btn[] = new Button("Home #" . $i . "\nNom: " . $homeName);
                $i++;
            }
            $namePlayer = $entity->getName();
            $xuidPlayer = $entity->getXuid();

            $player->sendForm(new MenuForm("§6- §fHome Manager §6-", "Voir et gérer les homes des joueurs, que demandé de plus ? :)",
                $btn, function (Player $player, Button $button) use ($homes, $namePlayer, $entity, $xuidPlayer): void {
                    $value = $button->getValue();
                    $i = 0;
                    $data = [];
                    foreach ($homes as $homeName => $posHash) {
                        if ($i === $value) {
                            $data = [
                                $posHash,
                                $homeName
                            ];
                        }
                        $i++;
                        if ($i >= 25) {
                            $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                            return;
                        }
                    }

                    if ($data !== []) {
                        $player->sendForm(new MenuForm("§6- §fHOME : §6" . $data[1] . " §6-", "Nom du joueur : " . $namePlayer, [
                            new Button("§6Se teleporter"),
                            new Button("§cSupprimer")
                        ], function (Player $player, Button $button) use ($data, $namePlayer, $entity, $xuidPlayer): void {
                            switch ($button->getValue()) {
                                case 0:
                                    $pos = $this->stringToPosition($data[0]);
                                    if ($pos !== null) {
                                        $player->teleport($pos);
                                        $player->sendMessage("§c[§4STAFF§c] §fTéléportation chez le home §c" . $data[1] . "§f du joueur §c" . $namePlayer);
                                        $player->sendSuccessSound();
                                    } else $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                                    break;
                                case 1:
                                    Main::getInstance()->getHomeManager()->deleteHome($xuidPlayer, $data[1]);
                                    $player->sendSuccessSound();
                                    $player->sendMessage("§c[§4STAFF§c]§f Home §c" . $data[1] . " §fdu joueur §c" . $namePlayer . " §fsupprimé !");
                                    break;
                            }
                        }));
                    } else $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                }));
        }
        return parent::onInteractEntity($player, $entity, $clickVector);
    }
}