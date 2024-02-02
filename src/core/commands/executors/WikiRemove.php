<?php

namespace core\commands\executors;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\managers\wiki\WikiManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class WikiRemove extends Executor
{

    public function __construct(string $name = "wikiremove", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('wiki.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $itemInHand = $sender->getInventory()->getItemInHand();

        if ($itemInHand->isNull()) {
            $sender->sendMessage(Messages::message("§cVous devez avoir un item dans votre main pour supprimer un wiki."));
            return;
        }

        $wikiManager = Main::getInstance()->getWikiManager();

        if (!$wikiManager->isWiki($itemInHand->getName())) {
            $sender->sendMessage(Messages::message("§cCet item n'a pas de wiki."));
            return;
        }

        $this->sendConfirmForm($sender, $itemInHand, $wikiManager);
    }

    private function sendConfirmForm(CustomPlayer $sender, Item $item, WikiManager $wikiManager): void
    {
        $sender->sendForm(new MenuForm("§cSupprimer le wiki", "§7Voulez-vous vraiment supprimer le wiki de l'item §c" . $item->getName() . "§7?",
            [
                new Button("§cOui"),
                new Button("§aNon")
            ],
            function (Player $player, Button $button) use ($item, $wikiManager): void {
                if ($button->getValue() === 0) {
                    $wikiManager->removeWiki($item->getName());
                    $player->sendMessage(Messages::message("§aVous avez supprimé le wiki de l'item §c" . $item->getName()));
                }
            }));
    }

}