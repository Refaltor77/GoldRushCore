<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Label;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Wiki extends Executor
{

    public function __construct(string $name = "wiki", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $itemInHand = $sender->getInventory()->getItemInHand();
        $wikiManager = Main::getInstance()->getWikiManager();

        if (empty($args[0])) {
            if ($itemInHand->isNull()) {
                $sender->sendMessage(Messages::message("§cVous devez avoir un item en main"));
                return;
            }
            if (count($wikiManager->getAll()) === 0) {
                $sender->sendMessage(Messages::message("§cAucun item n'est disponible dans le wiki"));
                return;
            }
            if (!$wikiManager->isWiki($itemInHand->getName())) {
                $sender->sendMessage(Messages::message("§cCet item n'est pas disponible dans le wiki"));
                return;
            }
            $this->sendWikiTypeInfosForm($sender, $wikiManager->getWiki($itemInHand->getName())["type"], $itemInHand->getName());
            return;
        }
        if ($args[0] === "help") {
            $sender->sendMessage(Messages::message("§b/wiki §7- §aAffiche le wiki de l'item en main\n" .
                "§b/wiki menu §7- §aaffiche le menu du wiki\n"
            ));
        }
        if ($args[0] === "menu") {
            $this->sendWikiForm($sender);
        }
    }

    private function sendWikiForm(CustomPlayer $sender): void
    {
        $wikiManager = Main::getInstance()->getWikiManager();

        $buttons = [];
        foreach ($wikiManager->getAllTypes() as $type) {
            $buttons[] = new Button($type);
        }

        $sender->sendForm(new MenuForm("Wiki", "§aChoisissez un type", $buttons,
            function (Player $player, Button $button) use ($wikiManager): void {
                $this->sendWikiTypeForm($player, $button->getText());
            }));
    }

    private function sendWikiTypeForm(CustomPlayer $player, string $type): void
    {
        $buttons = [];
        foreach (Main::getInstance()->getWikiManager()->getWikiByType($type) as $wiki) {
            $buttons[] = new Button($wiki["name"]);
        }
        $buttons[] = new Button("§cRetour");
        $player->sendForm(new MenuForm("Wiki - {$type}", "§aChoisissez un wiki", $buttons,
            function (Player $player, Button $button) use ($type): void {
                if ($button->getText() === "§cRetour") {
                    $this->sendWikiForm($player);
                    return;
                }
                $this->sendWikiTypeInfosForm($player, $type, $button->getText());
            }));
    }

    private function sendWikiTypeInfosForm(CustomPlayer $player, string $type, string $name): void
    {
        $wikiManager = Main::getInstance()->getWikiManager();
        $wiki = $wikiManager->getWikiByName($name);

        $infos = [
            new Label("§bDescription: §7{$wiki["description"]}"),
            new Label("§bRareté: §7{$wiki["rarity"]}"),
            new Label("§bType: §7{$wiki["type"]}")
        ];

        $player->sendForm(new CustomForm($name, $infos,
            function (Player $player, CustomFormResponse $response): void {

            }));
    }

}