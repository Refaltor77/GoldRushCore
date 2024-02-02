<?php

namespace core\listeners\types;

use core\api\gui\ChestInventory;
use core\api\gui\HopperInventory;
use core\listeners\BaseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;

class GuiApi extends BaseEvent
{
    public function onInventoryTransaction(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                $inventory = $action->getInventory();
                if ($inventory instanceof ChestInventory || $inventory instanceof HopperInventory) {
                    $clickCallback = $inventory->getClickCallback();
                    if ($clickCallback !== null) {
                        $clickCallback($player, $inventory, $action->getSourceItem(), $action->getTargetItem(), $action->getSlot());
                    }
                    if ($inventory->isCancelTransac()) {
                        $event->cancel();
                        $inventory->reloadTransac();
                    }
                    if ($inventory->isViewOnly()) {
                        $event->cancel();
                    }
                }
            }
        }
    }




    /*
     * public function onJsp2(SwapItemStackEvent $event): void {
        $player = $event->getPlayer();
        $inventory = $player->getCurrentWindow();
        if (!is_null($inventory)) {
            if ($inventory instanceof ChestInventory) {
                if ($inventory->isViewOnly()) $event->cancel();
            }
        }
    }


    public function onJsp(MoveItemStackEvent $event) {
        $player = $event->getPlayer();
        $inventory = $player->getCurrentWindow();

        if (!is_null($inventory)) {
            if ($inventory instanceof ChestInventory) {
                $callable = $inventory->getClickCallback();

                if (!empty($callable)) {
                 *
                    $callable($player, $inventory, $event->getDestination()->getSlotId(), $event->getSource()->getSlotId());
                    if ($inventory->isCancelTransac()) {
                        $inventory->reloadTransac();
                        $event->cancel();
                    }
                }
            }
        }
    }
     */
}