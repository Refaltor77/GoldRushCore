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
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class WikiAdd extends Executor
{

    public function __construct(string $name = "wikiadd", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('wiki.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $itemInHand = $sender->getInventory()->getItemInHand();

        if ($itemInHand->isNull()) {
            $sender->sendMessage(Messages::message("§cVous devez avoir un item dans votre main pour mettre à jour un wiki."));
            return;
        }
        $this->sendUpdateForm($sender, $itemInHand);
    }

    private function sendUpdateForm(CustomPlayer $sender, Item $itemInHand): void
    {
        $wikiManager = Main::getInstance()->getWikiManager();
        $isWiki = $wikiManager->isWiki($itemInHand->getName());
        $isWiki ? $wiki = $wikiManager->getWiki($itemInHand->getName()) : $wiki = null;

        $infos = [
            new Input("Description", "Entrez la description de l'item", $isWiki ? $wiki["description"] : ""),
            new Dropdown("Rareté", $wikiManager->getAllRarity(), $isWiki ? array_search($wiki["rarity"], $wikiManager->getAllRarity()) : 0),
            new Dropdown("Type", $wikiManager->getAllTypes(), $isWiki ? array_search($wiki["type"], $wikiManager->getAllTypes()) : 0)
        ];

        $sender->sendForm(new CustomForm($isWiki ? "§cMettre à jour le wiki" : "§cCréer un wiki un item", $infos,
            function (Player $player, CustomFormResponse $response) use ($itemInHand, $wiki): void {
                list($description, $rarity, $type) = $response->getValues();
                $this->sendConfirmForm($player, $itemInHand, $description, $rarity, $type);
            }));
    }

    private function sendConfirmForm(Player $player, Item $itemInHand, string $description, string $rarity, string $type): void
    {
        $buttons = [
            new Button("§aConfirmer"),
            new Button("§cAnnuler")
        ];
        $player->sendForm(new MenuForm("§cConfirmer la mise à jour du wiki", "§cVoulez-vous vraiment mettre à jour le wiki de l'item §b" . $itemInHand->getName() . "§c ?", $buttons,
            function (Player $player, Button $button) use ($itemInHand, $description, $rarity, $type): void {
                $wikiManager = Main::getInstance()->getWikiManager();
                $wikiManager->updateWiki($itemInHand->getName(), $description, $rarity, $type);
                $player->sendMessage(Messages::message("§aVous avez mis à jour le wiki de l'item §b" . $itemInHand->getName() . "§a."));
            }, function (Player $player) use ($itemInHand): void {
                $player->sendMessage(Messages::message("§cVous avez annulé la mise à jour du wiki de l'item §b" . $itemInHand->getName() . "§c."));
            }));
    }
}