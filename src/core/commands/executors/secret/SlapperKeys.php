<?php

namespace core\commands\executors\secret;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\cinematic\Cinematics;
use core\commands\Executor;
use core\managers\box\BoxManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class SlapperKeys extends Executor
{
    public function __construct(string $name = 'slapper_keys', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $sender->sendForm(new MenuForm("OPEN_BOX", "", [
            new Button("Common"),
            new Button("Rare"),
            new Button("Boost"),
            new Button("Fortune"),
            new Button("Mythical"),
            new Button("Legendary"),
            new Button("Black Gold")
        ], function (Player $player, Button $button): void {
            $keysItem = match ($button->getValue()) {
                0 => CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON),
                1 => CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE),
                2 => CustomiesItemFactory::getInstance()->get(Ids::KEY_BOOST),
                3 => CustomiesItemFactory::getInstance()->get(Ids::KEY_FORTUNE),
                4 => CustomiesItemFactory::getInstance()->get(Ids::KEY_MYTHICAL),
                5 => CustomiesItemFactory::getInstance()->get(Ids::KEY_LEGENDARY),
                6 => CustomiesItemFactory::getInstance()->get(Ids::KEY_BLACK_KEY),
            };

            $boxType = match ($button->getValue()) {
                0 => BoxManager::COMMON,
                1 => BoxManager::RARE,
                2 => BoxManager::BOOST,
                3 => BoxManager::FORTUNE,
                4 => BoxManager::MYTHICAL,
                5 => BoxManager::LEGENDARY,
                6 => BoxManager::BLACK_GOLD,
            };

            if (!$player->getInventory()->canAddItem(VanillaItems::STICK()->setCount(64))) {
                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                return;
            }


            if (!$player->getInventory()->contains($keysItem)) {
                $player->sendMessage(Messages::message("§cVous ne possédez pas la clé correspondante à la box que vous souhaitez ouvrir."));
                return;
            }

            if ($player->isInCinematic) {
                $player->sendMessage(Messages::message("§cVous ête déjà dans une cinématique."));
                return;
            }

            $player->sendMessage(Messages::message("§fPréparation à l'ouverture..."));
            Cinematics::sendDoorOpenCinematic($player, $boxType, $keysItem);
        }));
    }
}