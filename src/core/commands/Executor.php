<?php

namespace core\commands;

use core\events\LogEvent;
use core\Main;
use core\messages\Messages;
use core\messages\Prefix;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\types\command\ChainedSubCommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\Server;

abstract class Executor extends CustomCommands
{

    use UtilsTrait;

    public int $VisibilityPermission;


    public function __construct(string $name, string $description = "", string|null $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        # permission message is not use.
        $this->setPermissionMessage("§cVous n'avez pas la permission");
        $this->VisibilityPermission = $permission;
        $this->setPermission("base.permission.user");
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function setPermission(?string $permission): void
    {
        if (!is_null($permission)) {
            if ($permission !== "base.permission.user") {
                Server::getInstance()->getLogger()->info("[PERMISSION]: " . $permission);
            }
            PermissionManager::getInstance()->addPermission(new Permission($permission));
        }
        parent::setPermission($permission);
    }

    public function getPlugin(): Main
    {
        return Main::getInstance();
    }


    public function testPermission(CommandSender $target, ?string $permission = null): bool
    {
        if(Server::getInstance()->isOp($target->getName()) || $target instanceof ConsoleCommandSender) return true;
        foreach ($this->getPermissions() as $permission) {
            $hasPermission = $this->testPermissionSilent($target, $permission);
            if (!$hasPermission) {
                if ($target instanceof CustomPlayer) {
                    $target->sendMessage("Vous n'avez pas la permission.",true,  success: false);
                }
                return false;
            }
        }
        return true;
    }


    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof ConsoleCommandSender) {
            $this->onRunConsoleCommandSender($sender, $commandLabel, $args);
            return;
        }
        if (!$sender->hasReallyConnected) return;


        if ($sender->hasTagged()) {
            if (!$sender->isOp()) {
                $sender->sendNotification("§cVous êtes en combat.", CustomPlayer::NOTIF_TYPE_HUNTER);
                return;
            }
        }

        if (Main::getInstance()->getExchangeManager()->hasSaveInventory($sender)) {
            if (in_array($this->getName(), [
                'exchange_refuse_12457586',
                'exchange_accept_12457586',
                'exchange_cancel_12457586',
                'pay'
            ])) {
                $this->onRun($sender, $commandLabel, $args);
                return;
            } else {
                $sender->sendNotification("§cVous êtes actuellement en salle d'échange sécurisé.");
                return;
            }
        }


        if ($sender->hasFreeze()) {
            $sender->sendNotification("§cVous êtes freeze !");
            return;
        }
        (new LogEvent($sender->getName()." à éxécute la commande [".$this->getName() . "]","COMMANDS"))->call();

        $this->onRun($sender, $commandLabel, $args);
    }

    public function onRunConsoleCommandSender(ConsoleCommandSender $sender, string $commandLabel, array $args){}

    abstract public function onRun(CustomPlayer $sender, string $commandLabel, array $args);

    public function reloadArgument(?Player $player = null): CommandData
    {
        return $this->loadOptions($player);
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return $this->getCommandData();
    }

    public function getCommandData(): CommandData
    {
        $overloads = [];
        foreach ($this->overload as $commandParameters) {
            $overloads[] = new CommandOverload(true, $commandParameters);
        }

        return new CommandData(
            $this->getName(),
            $this->getDescription(),
            0,
            $this->getVisibilityPermission(),
            null,
            $overloads,
            []
        );
    }

    public function getVisibilityPermission(): int
    {
        return $this->VisibilityPermission;
    }
}