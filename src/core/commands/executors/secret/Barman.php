<?php

namespace core\commands\executors\secret;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Input;
use core\api\form\MenuForm;
use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
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
use pocketmine\world\sound\XpLevelUpSound;

class Barman extends Executor
{
    use SoundTrait;

    public function __construct(string $name = '1234567891_barman', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {

        $sender->sendForm(new MenuForm("BARMAN", "§6Besoin d'un remontant ?§7 Je vous propose ma gamme d'alcools puissants ! Mes boissons sont uniques, leurs saveurs ne passe pas inaperçue..", [
            new Button("SOIN"), // 5k/u
            new Button("FORCE"),  // 35k/u
            new Button("HASTE"), // 5k/u
            new Button("SPEED"), // 15k/u
        ], function (Player $player, Button $button): void {
            $arrayValues = [
                0 => [
                    'item' => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL),
                    'money' => 5000
                ],
                1 => [
                    'item' => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE),
                    'money' => 35000
                ],
                2 => [
                    'item' => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HASTE),
                    'money' => 5000
                ],
                3 => [
                    'item' => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED),
                    'money' => 15000
                ],
            ];

            $data = $arrayValues[$button->getValue()];

            $item = $data['item'];
            $money = $data['money'];

            $player->sendForm(new CustomForm("Choisisez le montant", [
                new Input("Montant", "64")
            ], function (Player $player, CustomFormResponse $response) use ($money, $item) : void {
                $value = $response->getInput()->getValue();

                if (!(int)$value) {
                    $player->sendMessage(Messages::message("§cVVous devez mettre une valeur numérique."));
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
                    $player->sendMessage("§6[§fBarman§6]§f : Merci pour ton achat §a" . $player->getName() . "§f !");
                });
            }));
        }));
    }
}