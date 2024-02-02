<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Dropdown;
use core\api\form\elements\Input;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Blacklist extends Executor
{

    public function __construct(string $name = "blacklist", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("blacklist.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (empty($args[0])) {
            $sender->sendMessage("§cUsage: /blacklist <add|remove>");
            return;
        }
        $blacklistManager = Main::getInstance()->getBlacklistManager();
        if ($args[0] === "remove") {
            $sender->sendForm(new CustomForm("§l§e» §r§6Blacklist", [
                new Dropdown("blacklist", $blacklistManager->getBlackList())
            ], function (Player $player, CustomFormResponse $response): void {
                list($value) = $response->getValues();
                $this->confirmAction($player, "enlever", $value);
            }));
        } else if ($args[0] === "add") {
            $sender->sendForm(new CustomForm("§l§e» §r§6Blacklist", [
                new Input("blacklist", "Entrer un mot")
            ], function (Player $player, CustomFormResponse $response): void {
                list($value) = $response->getValues();
                $this->confirmAction($player, "ajouter", $value);
            }));
        }
    }

    private function confirmAction(Player $player, string $choice, string $value): void
    {
        $blackListManager = Main::getInstance()->getBlacklistManager();
        $player->sendForm(new MenuForm("§l§e» §r§6Blacklist", "§7Êtes-vous sûr $choice le mot $value ?",
            [
                new Button("§l§aOui"),
                new Button("§l§cNon")
            ],
            function (Player $p, Button $button) use ($blackListManager, $choice, $value): void {
                if ($button->getValue() === 0) {
                    if ($choice === "ajouter") {
                        $blackListManager->addBlackList($value);
                    } else if ($choice === "enlever") {
                        $blackListManager->removeBlackList($value);
                    }
                    $p->sendMessage("§aLe mot $value a été $choice à la blacklist.");
                }
            }
        ));
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'add');
        $this->addSubCommand(1, 'remove');

        $this->addComment(0, 1, 'Ajouter un mot blacklist');
        $this->addComment(1, 1, 'Retirer un mot blacklist');
        return parent::loadOptions($player);
    }
}