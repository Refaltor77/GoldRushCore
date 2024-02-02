<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\commands\Executor;
use core\items\horse\HorseArmorAmethyst;
use core\items\horse\HorseArmorCopper;
use core\items\horse\HorseArmorEmerald;
use core\items\horse\HorseArmorGold;
use core\items\horse\HorseArmorPlatinum;
use core\Main;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class Hdv extends Executor
{
    public static array $hasInHdvForm = [];

    public function __construct(string $name = 'hdv', string $description = "Ouvrir l'hôtel des ventes", ?string $usageMessage = null, array $aliases = [
        'market', 'hdv'
    ])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        $api = Main::getInstance()->getHdvManager();
        if (isset($args[0])) {
            if ($args[0] === 'sell') {
                $itemInHand = $sender->getInventory()->getItemInHand();
                if ($itemInHand->isNull()) {
                    $sender->sendMessage(Messages::message("§cVous ne pouvez pas vendre de l'air."));
                    return;
                }
                if (in_array($itemInHand::class, [
                    HorseArmorCopper::class,
                    HorseArmorEmerald::class,
                    HorseArmorAmethyst::class,
                    HorseArmorPlatinum::class,
                    HorseArmorGold::class
                ])) {
                    $sender->sendErrorSound();
                    $sender->sendMessage(Messages::message("§cVous ne pouvez pas vendre de montures."));
                    return;
                }
                $name = $itemInHand->getVanillaName();
                $amount = $itemInHand->getCount();
                self::$hasInHdvForm[$sender->getXuid()] = true;
                $sender->sendForm(new CustomForm(
                    '- §6Mise en vente §r-',
                    [
                        new Label("§7Bienvenue sur la création de votre annonce dans l'hôtel des ventes, vous pouvez personnaliser votre prix de vente.\n\n§6» §6Nom de l'item:§e $name\n§6» §6Montant: §e$amount\nTaxe : 5%"),
                        new Input("§6» §ePrix de vente", "1000")
                    ],
                    function (Player $player, CustomFormResponse $response) use ($api, $itemInHand): void {
                        $item = $player->getInventory()->getItemInHand();
                        if (!$item->equalsExact($itemInHand)) {
                            $player->sendMessage(Messages::message("§cVous avez changé d'item en main."));
                            return;
                        }
                        if ($item->isNull()) {
                            $player->sendMessage(Messages::message("§cVous ne pouvez pas vendre de l'air."));
                            return;
                        }

                        list($price) = $response->getValues();
                        self::$hasInHdvForm[$player->getXuid()] = false;
                        if (!(int)$price) {
                            $player->sendMessage(Messages::message("§cVous devez préciser un prix en chiffre."));
                            return;
                        }

                        if ($price > 1000000000) {
                            $player->sendMessage(Messages::message("§cLe prix est trop grand."));
                            return;
                        }

                        if ($price <= 0) {
                            $player->sendMessage(Messages::message("Vous ne pouvez pas mettre un chiffre négatif."));
                            return;
                        }

                        $cinqPourcent = intval($price * 0.05);

                        Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $money) use ($cinqPourcent, $price, $item, $api) : void {
                            if ($money < $cinqPourcent) {
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent pour payer la taxe de §4" . $cinqPourcent . "$ !"));
                                return;
                            }

                            Main::getInstance()->getEconomyManager()->removeMoney($player, $cinqPourcent);
                            $player->getInventory()->removeItem($item);
                            $api->createProduct($item, $price, $player);
                        });

                    }
                    , function (Player $player): void {
                    self::$hasInHdvForm[$player->getXuid()] = false;
                }));
            } elseif ($args[0] === 'show') {
                $api->display($sender);
            } else $sender->sendMessage(Messages::message("§c/hdv <show:sell>"));
        } else $api->display($sender);
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'sell');
        $this->addSubCommand(1, 'show');
        return parent::loadOptions($player);
    }
}