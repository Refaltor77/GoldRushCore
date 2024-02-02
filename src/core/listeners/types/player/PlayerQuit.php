<?php

namespace core\listeners\types\player;

use core\commands\executors\staff\Troll;
use core\events\LogEvent;
use core\listeners\BaseEvent;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;
use pocketmine\world\Position;

class PlayerQuit extends BaseEvent
{
    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if (!$player instanceof CustomPlayer) return;
        if (!$player->hasReallyConnected) return;
        if ($player->hasTagged()) {
            $items = array_merge($player->getInventory()->getContents(), $player->getArmorInventory()->getContents());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $world = $player->getWorld();
            $pos = $player->getPosition();
            foreach ($items as $index => $item) {
                $world->dropItem($pos, $item);
            }
        }

        if ($player->isInCinematic) {
            $player->teleport(new Position(7, 139, 90, Server::getInstance()->getWorldManager()->getDefaultWorld()));
        }


        $player->quit = true;


        if (Main::getInstance()->getExchangeManager()->hasSaveInventory($player)) {
            Main::getInstance()->getExchangeManager()->cancelSession($player);
        }

        $event->setQuitMessage("§7[§c-§7] §7" . $player->getName());



        if (Main::getInstance()->getServer()->isRunning()) {
            Main::getInstance()->getConnexionManager()->disconnect($player);
            if ($player->hasJobsLoaded) $this->getPlugin()->getJobsManager()->saveData($player);
            if ($player->hasInvLoaded) {
                if (isset(Troll::$cache[$player->getXuid()])) {
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->getEnderInventory()->clearAll();
                    $player->getOffHandInventory()->clearAll();
                    unset(Troll::$cache[$player->getXuid()]);
                } else $this->getPlugin()->getInventoryManager()->saveInventory($player);
            }
            if ($player->hasStatsLoaded)$this->getPlugin()->getStatsManager()->saveData($player);
            if ($player->hasDataLoaded)$this->getPlugin()->getDataManager()->saveUser($player);
            if ($player->hasRankLoaded) $this->getPlugin()->getRankManager()->saveData($player);
            if ($player->hasHomeLoaded) $this->getPlugin()->getHomeManager()->saveData($player);
            if ($player->hasEconomyLoaded) $this->getPlugin()->getEconomyManager()->saveData($player);
            if ($player->hasEnderLoaded) $this->getPlugin()->getEnderChestManager()->saveUser($player, true);


            if ($player->hasJobsStorageLoaded) $this->getPlugin()->jobsStorage->saveUserCache($player, true, null, true);



            if ($player->hasQuestStorageLoaded) $this->getPlugin()->questStorage->saveUserCache($player);
            if ($player->hasSettingsLoaded)$this->getPlugin()->getSettingsManager()->saveData($player);
            if ($player->hasSoreboardLoaded)$this->getPlugin()->getScoreboardManager()->saveData($player);
            $this->getPlugin()->getSkinManager2()->saveData($player);
            $this->getPlugin()->getXpManager()->saveData($player);
            if ($player->hasPrimeLoaded)$this->getPlugin()->getPrimeManager()->saveData($player);
            $this->getPlugin()->getGrafanaManager()->addSessionTimeEnd($player->getXuid(), time() - $player->getNetworkSession()->connectTime);
            Main::getInstance()->getBlockBreakManager()->saveData($player);
        }



        if ($player->hasFreeze()) {
            Main::getInstance()->getSanctionManager()->ban($player->getXuid(), "Déconnexion Freeze", 60 * 60 * 24 * 90);
        }


        (new LogEvent($player->getName()." a quitté le serveur",LogEvent::LEAVE_TYPE))->call();
    }
}