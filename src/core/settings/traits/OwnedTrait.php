<?php

namespace core\settings\traits;

use core\commands\CustomCommands;
use core\listeners\BaseEvent;
use core\Main;
use core\managers\data\DataManager;
use core\managers\dc\DcManager;
use core\managers\economy\EconomyManager;
use core\managers\factions\FactionManager;
use core\managers\homes\HomeManager;
use core\managers\inventory\InventoryManager;
use core\managers\jobs\JobsManager;
use core\managers\ranks\RankManager;
use core\managers\sanctions\SanctionManager;
use core\managers\stats\StatsManager;
use core\thread\ThreadManager;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

trait OwnedTrait
{
    private Main $plugin;

    public function setPlugin(Main $plugin): void {$this->plugin = $plugin;}
    public function getPlugin(): Main { return $this->plugin;}

    public function getManagerData(): DataManager { return $this->getPlugin()->getDataManager(); }
    public function getManagerEconomy(): EconomyManager { return $this->getPlugin()->getEconomyManager(); }
    public function getManagerStats(): StatsManager { return $this->getPlugin()->getStatsManager(); }
    public function getManagerRanks(): RankManager { return $this->getPlugin()->getRanksManager(); }
    public function getManagerFactions(): FactionManager { return $this->getPlugin()->getFactionsManager(); }
    public function getManagerSanctions(): SanctionManager { return $this->getPlugin()->getSanctionsManager(); }
    public function getManagerInventory(): InventoryManager { return $this->getPlugin()->getInventoryManager(); }
    public function getManagerThread(): ThreadManager { return $this->getPlugin()->getThreadManager(); }
    public function getManagerJobs(): JobsManager { return $this->getPlugin()->getJobsManager(); }
    public function getManagerHome(): HomeManager { return $this->getPlugin()->getHomeManager(); }
    public function getDcManager(): DcManager { return $this->getPlugin()->getDcManager(); }


    public function registerEvent(BaseEvent|Listener $event): void {
        $this->getPlugin()->getServer()->getPluginManager()->registerEvents($event, $this->getPlugin());
    }

    public function createPacket(Player $player): AvailableCommandsPacket
    {
        $pk = new AvailableCommandsPacket();
        foreach (Server::getInstance()->getCommandMap()->getCommands() as $commandName => $commandData) {
            if (class_parents($commandData)[array_key_first(class_parents($commandData))] === "core\commands\manager\Executor") {
                /** @var CustomCommand $commandData */
                if ($commandData->getVisibilityPermission() === PlayerPermissions::OPERATOR && !Server::getInstance()->isOp($player->getName())) continue;
                if (str_contains($commandName, 'goldrush' . ":")) {
                    $pk->commandData[$commandName] = $commandData->reloadArgument();
                } else {
                    $pk->commandData[('goldrush' . ":" . $commandName)] = $commandData->reloadArgument();
                }

            } else {
                /** @var Command $commandData */
                if (!$commandData->testPermissionSilent($player, $commandData->getPermission())) continue;
                if ($commandData->getDescription() instanceof Translatable) {
                    $des = $player->getLanguage()->translate($commandData->getDescription());
                } else {
                    $des = $commandData->getDescription();
                }
                $pk->commandData[$commandName] = new CommandData($commandData->getName(), $des, 0, 0, null, []);

            }
        }
        return $pk;
    }

    public function setTimeout(callable $function, int $ticks): void {
        $this->getPlugin()->getScheduler()->scheduleDelayedTask(new ClosureTask($function), $ticks);
    }

    public function nextTick(callable $function): void {
        $this->setTimeout($function, 20);
    }
}