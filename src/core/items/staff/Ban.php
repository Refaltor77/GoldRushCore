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

class Ban extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Marteau du ban';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('ban', $inventory);
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Ban des joueurs",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "GURIDO LE MEC A REFA"
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
                '§6- §fGoldRush §6B§fannissement §6-',
                [
                    new Label('§7Vous êtes sur le point de bannir le joueur §f' . $entity->getName()),
                    new Input('§6» §fRaison', 'X-Ray'),
                    new Input('§6» §fDurée', ""),
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


                    Main::getInstance()->getSanctionManager()->addWarn($entity->getXuid(), 'Bannissement pour ' . $reason);
                    Main::getInstance()->getSanctionManager()->ban($entity->getXuid(), $reason, intval($timestamp));
                    if ($entity->isConnected()) {
                        $msg = "§6Vous venez d'être bannie de GoldRush\n";
                        $msg .= "§fReason: §6$reason\n";
                        $msg .= "§fDate de fin: §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", $reste) . "\n";
                        $msg .= "§fDiscord: §6https://discord.gg/goldrush";
                        $entity->kick($msg);
                    }
                    Main::getInstance()->getGrafanaManager()->addBanQueue($entity->getXuid(), $sender->getXuid(), $reason, intval($timestamp));
                    $sender->sendMessage(Messages::message("§aVous avez banni le joueur §f{$entity->getName()}"));
                    (new LogEvent($sender->getName() . " a banni " . $entity->getName() . " pour la raison " . $reason . " et pour la durée de " . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)), LogEvent::SANCTION_TYPE))->call();
                }
            ));
        }
        return false;
    }
}