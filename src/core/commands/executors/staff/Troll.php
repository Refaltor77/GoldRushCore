<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class Troll extends Executor
{
    public static array $cache = [];

    public function __construct()
    {
        parent::__construct("troll", "troll", "/troll");
        $this->setPermission("troll.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (isset(self::$cache[$sender->getXuid()])) {
            unset(self::$cache[$sender->getXuid()]);
            Main::getInstance()->getInventoryManager()->checkingDatabase($sender);
            $sender->sendMessage(Messages::message("§aTrollMod désactivé !"));
        } else {
            self::$cache[$sender->getXuid()] = 1;
            $sender->sendMessage(Messages::message("§aTrollMod activé !"));
            Main::getInstance()->getInventoryManager()->saveInventory($sender, true, true);
            $sender->getInventory()->setItem(0, VanillaItems::REDSTONE_DUST()->setCustomName("§c<=="));
            $sender->getInventory()->setItem(1, VanillaItems::EMERALD()->setCustomName("§oDrop inventory"));
            $sender->getInventory()->setItem(2, VanillaBlocks::TNT()->asItem()->setCustomName("§oBoom"));
            $sender->getInventory()->setItem(3, VanillaBlocks::FIRE()->asItem()->setCustomName("§oBurn"));
            $sender->getInventory()->setItem(4, VanillaItems::PAPER()->setCustomName("§oLigthning"));
            $sender->getInventory()->setItem(5, VanillaItems::HEART_OF_THE_SEA()->setCustomName("§oNoob"));
            $sender->getInventory()->setItem(6, VanillaBlocks::BARRIER()->asItem()->setCustomName("§oNo break block"));
            $sender->getInventory()->setItem(7, VanillaItems::DIAMOND_PICKAXE()->setCustomName("§oFake ban"));
            $sender->getInventory()->setItem(8, VanillaItems::REDSTONE_DUST()->setCustomName("§c==>"));
        }
    }
}