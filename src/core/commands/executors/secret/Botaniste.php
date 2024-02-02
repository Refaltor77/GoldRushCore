<?php

namespace core\commands\executors\secret;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Input;
use core\api\form\MenuForm;
use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\items\crops\BerryBlack;
use core\items\crops\BerryBlue;
use core\items\crops\BerryPink;
use core\items\crops\BerryYellow;
use core\items\crops\Raisin;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\XpLevelUpSound;

class Botaniste extends Executor
{
    use SoundTrait;

    public function __construct(string $name = '1234567891_botaniste', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $sender->sendForm(new MenuForm("BOTANISTE", "Besoin d'aide pour vos végétaux ? Bienvenue chez moi ! Je suis le meilleur botaniste de GoldRush, tu ne trouveras pas mieux ailleurs :)", [
            new Button("raisin"),
            new Button("baie rose"),
            new Button("baie noir"),
            new Button("baie jaune"),
            new Button("baie bleu"),
            new Button("fleur de camouflage"),
        ], function (Player $player, Button $button): void {
            $money = match ($button->getText()) {
                "raisin" => 300,
                "baie rose" => 500,
                "baie jaune" => 500,
                "baie noir" => 500,
                "baie bleu" => 500,
                "fleur de camouflage" => 500000,
            };

            $item = match ($button->getText()) {
                "raisin" => CustomiesItemFactory::getInstance()->get(Ids::RAISIN),
                "baie rose" => CustomiesItemFactory::getInstance()->get(Ids::BERRY_PINK),
                "baie jaune" => CustomiesItemFactory::getInstance()->get(Ids::BERRY_YELLOW),
                "baie noir" => CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLACK),
                "baie bleu" => CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLUE),
                "fleur de camouflage" => CustomiesItemFactory::getInstance()->get(Ids::FLOWER_PERCENT),
            };


            if (in_array($item::class, [
                BerryBlue::class,
                BerryPink::class,
                BerryBlack::class,
                BerryYellow::class,
                Raisin::class
            ])) {
                $player->sendForm(new CustomForm("Choisisez le montant", [
                    new Input("Montant", "64")
                ], function (Player $player, CustomFormResponse $response) use ($money, $item) : void {
                    $value = $response->getInput()->getValue();

                    if (!(int)$value) {
                        $player->sendMessage(Messages::message("§cVVous devez mettre une valeur numérique."));
                        $player->sendErrorSound();
                        return;
                    }

                    if($value < 1) {
                        $player->sendMessage(Messages::message("§cVous devez mettre une valeur supérieur à 0."));
                        $player->sendErrorSound();
                        return;
                    }

                    $item->setCount($value);

                    if (!$player->getInventory()->canAddItem($item)) {
                        $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                        $player->sendErrorSound();
                        return;
                    }

                    $money = $money * $value;

                    Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $moneyPlayer) use ($money, $item) : void  {
                        if ($moneyPlayer < $money) {
                            $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent."));
                            $player->sendErrorSound();
                            return;
                        }

                        if (!$player->getInventory()->canAddItem($item)) {
                            $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                            $player->sendErrorSound();
                            return;
                        }

                        Main::getInstance()->getEconomyManager()->removeMoney($player, $money);
                        $player->getInventory()->addItem($item);
                        $player->sendSuccessSound();
                        $player->sendMessage("§a[§fBotaniste§a]§f : Merci pour ton achat §a" . $player->getName() . "§f !");
                    });
                }));
                return;
            }

            if (!$player->getInventory()->canAddItem($item)) {
                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                $player->sendErrorSound();
                return;
            }

            Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $moneyPlayer) use ($money, $item) : void  {
                if ($moneyPlayer < $money) {
                    $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent."));
                    $player->sendErrorSound();
                    return;
                }

                if (!$player->getInventory()->canAddItem($item)) {
                    $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                    $player->sendErrorSound();
                    return;
                }

                Main::getInstance()->getEconomyManager()->removeMoney($player, $money);
                $player->getInventory()->addItem($item);
                $player->sendSuccessSound();
                $player->sendMessage("§a[§fBotaniste§a]§f : Merci pour ton achat §a" . $player->getName() . "§f !");
            });
        }));
    }
}