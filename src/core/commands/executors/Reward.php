<?php

namespace core\commands\executors;

use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Reward extends Executor
{
    public function __construct(string $name = 'rewards', string $description = "Voir vos récompenses de métiers", ?string $usageMessage = null, array $aliases = [
        'reward'
    ], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $inv = Main::getInstance()->jobsStorage->getInventoryPlayerJobs($sender);
        $double = new DoubleChestInventory();
        $double->setName("Vos récompenses");


        $notAdd = [];
        foreach ($inv as $item) {
            if ($double->canAddItem($item)) {
                $double->addItem($item);
            } else $notAdd[] = $item;
        }


        $double->setClickCallback(function (Player $player, DoubleChestInventory $inventoryCustom, Item $sourceItem, Item $targetItem, int $slot): void {
            if ($targetItem->getTypeId() !== VanillaItems::AIR()->getTypeId()) $inventoryCustom->transacCancel();
        });

        $double->setCloseCallback(function (Player $player, Inventory $inventory) use ($double, $notAdd): void {
            $content = array_merge($double->getContents(), $notAdd);
            Main::getInstance()->jobsStorage->setInv($player, $content);
        });

        $double->send($sender);

        (new LogEvent($sender->getName()." a ouvert ses récompenses", LogEvent::JOB_TYPE))->call();

    }
}