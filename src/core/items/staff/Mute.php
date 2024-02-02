<?php

namespace core\items\staff;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Dropdown;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\events\LogEvent;
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

class Mute extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Marteau du mute';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('mute', $inventory);
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Mute des joueurs",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "REFA LE BG"
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
            $player->sendForm(new CustomForm(
                '§6- §fGoldRush §6M§fute §6-',
                [
                    new Label('§fVous êtes sur le point de mute le joueur §6' . $entity->getName()),
                    new Input('§6» §eRaison', 'Insultes'),
                    new Input('§6» §eDurée', "5"),
                    new Dropdown("", ['Minutes', 'Heures', 'Jours', 'Mois'], 1),
                ],
                function (Player $sender, CustomFormResponse $response) use ($entity): void {
                    if (!$entity->isConnected()) {
                        $sender->sendMessage(Messages::message("§cLe joueur s'est déconnecté"));
                        return;
                    }
                    list($reason, $timestamp, $type) = $response->getValues();

                    if (!(int)$timestamp) {
                        $sender->sendMessage(Messages::message("§cVous devez préciser une valeur de temps en chiffre."));
                        return;
                    }

                    $timestamp *= match ($type) {
                        'Minutes' => 60,
                        'Heures' => 3600,
                        'Jours' => 86400,
                        'Mois' => 2628000,
                    };
                    Main::getInstance()->getSanctionManager()->addWarn($entity->getXuid(), 'Mute pour ' . $reason);
                    Main::getInstance()->getSanctionManager()->mute($entity->getXuid(), $reason, intval($timestamp));
                    Main::getInstance()->getGrafanaManager()->addMuteQueue($entity->getXuid(), $sender->getXuid(), $reason, intval($timestamp));
                    $sender->sendMessage(Messages::message("§aVous avez mute le joueur §6{$entity->getName()}"));

                    foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $players) {
                        if ($players->getName() !== $entity->getName()) $players->sendMessage("§6------\n§6- §6GoldRush Mute§6 -\n§fJoueur: §6{$entity->getName()}\n§fDate de fin: §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)) . "\n§fRaison: §c" . $reason . "\n§6------");
                    }
                    (new LogEvent($sender->getName() . " a mute " . $entity->getName() . " pour la raison " . $reason . " et pour la durée de " . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)), LogEvent::SANCTION_TYPE))->call();
                }
            ));
        }
        return parent::onInteractEntity($player, $entity, $clickVector);
    }
}