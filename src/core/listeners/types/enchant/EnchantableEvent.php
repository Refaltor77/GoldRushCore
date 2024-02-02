<?php

namespace core\listeners\types\enchant;

use core\inventory\EnchantInventory;
use core\listeners\BaseEvent;
use pocketmine\event\player\PlayerEnchantingOptionsRequestEvent;
use pocketmine\item\enchantment\EnchantingHelper;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\network\mcpe\protocol\types\EnchantOption;
use pocketmine\utils\Random;

class EnchantableEvent extends BaseEvent
{
    public function onEnchant(PlayerEnchantingOptionsRequestEvent $event): void {
        $inventory = $event->getInventory();
        $player = $event->getPlayer();
        if ($inventory instanceof EnchantInventory) {
            $random = new Random($player->getXpManager()->getCurrentTotalXp());
            $bookshelfCount = $inventory->countBookshelves();
            $baseCost = ($random->nextBoundedInt(8) + 1) + floor($bookshelfCount >> 1) + $random->nextBoundedInt($bookshelfCount + 1);
            $topCost = floor(max($baseCost / 3, 1));
            $middleCost = floor($baseCost * 2 / 3 + 1);
            $bottomCost = floor(max($baseCost, $bookshelfCount * 2));


            $event->setOptions([
                $inventory->createOption($random, $inventory->getInput(), $topCost),
                $inventory->createOption($random, $inventory->getInput(), $middleCost),
                $inventory->createOption($random, $inventory->getInput(), $bottomCost),
            ]);
        }
    }
}