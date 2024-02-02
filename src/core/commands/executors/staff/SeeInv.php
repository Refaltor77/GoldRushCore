<?php

namespace core\commands\executors\staff;

use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class SeeInv extends Executor
{
    public function __construct(string $name = 'seeinv', string $description = "Voir l'inventaire d'un joueur", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('seeinv.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/seeinv <playerName>"));
            return;
        }

        $entity = Server::getInstance()->getPlayerByPrefix($args[0]);
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
            $inv->send($sender);
        } else {



            $xuid = Main::getInstance()->getDataManager()->getXuidByName($args[0]);
            if ($xuid === null) {
                $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne"));
                return;
            }



            Main::getInstance()->getInventoryManager()->checkingDatabasePlayerXuid($xuid, function (array $invContent, array $armorInv, array $offHand, array $ecInv) use ($sender, $args) : void {
                if (!$sender->isConnected()) return;
                $itemsBarrier = VanillaBlocks::BARRIER()->asItem();
                $inv = new DoubleChestInventory();
                $inv->setName($args[0]);
                $inv->setViewOnly();
                $inv->setContents($invContent);
                $slots = [45, 46, 48, 50, 52, 53, 36, 37, 38, 39, 40, 41, 42, 43, 44];
                foreach ($slots as $slot) $inv->setItem($slot, $itemsBarrier);
                $inv->setItem(47, $armorInv[0] ?? VanillaItems::AIR());
                $inv->setItem(49, $armorInv[1] ?? VanillaItems::AIR());
                $inv->setItem(51, $armorInv[2] ?? VanillaItems::AIR());
                $inv->setItem(53, $armorInv[3] ?? VanillaItems::AIR());
                $inv->send($sender);
            });
        }
    }


    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, "Liste des joueurs a", true, 'Joueurs a', Main::getInstance()->getDataManager()->getAllNameInDatabaseForArgs());
        return parent::loadOptions($player);
    }
}