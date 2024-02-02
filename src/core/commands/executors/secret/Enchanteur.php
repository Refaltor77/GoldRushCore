<?php

namespace core\commands\executors\secret;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Enchanteur extends Executor
{
    public function __construct(string $name = "enchanteur", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $form = new MenuForm("ENCHANTEUR",
            TextFormat::LIGHT_PURPLE . "Besoin d'un coup de pouce pour enchanter tes outils ? ".TextFormat::WHITE."Salut ! Je suis Onlie Buck, l'enchanteur le plus reconnu de la région ! Donne-moi de l'argent, et en échange, tes objets seront resplendissants.",
        [
            new Button("one"),
            new Button("two"),
            new Button("three"),
        ], function (Player $player, Button $button): void  {
            $money = match ($button->getText()) {
                "one" => 1000,
                "two" => 15000,
                "three" => 30000
            };

            $xp = match ($button->getText()) {
                "one" => 1,
                "two" => 15,
                "three" => 30
            };



            Main::getInstance()->getEconomyManager()->getManagerEconomy()->getMoneySQL($player, function (Player $player, int $moneyPlayer) use ($money, $xp) : void {
                if ($moneyPlayer < $money) {
                    $player->sendMessage(Messages::message("§cVous n'avez pas §4" . $money . "$"));
                    $player->sendErrorSound();
                    return;
                }

                $player->sendSuccessSound();
                $player->sendMessage(Messages::message("§f+ §6" . $xp . " éxperience"));
                $player->getXpManager()->addXpLevels($xp);
                Main::getInstance()->getEconomyManager()->removeMoney($player, $money);
            });
        });

        $sender->sendForm($form);
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}