<?php

namespace core\commands\executors\secret;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Input;
use core\api\form\MenuForm;
use core\api\gui\DoubleChestInventory;
use core\cinematic\Cinematics;
use core\commands\Executor;
use core\entities\BoxBlackGold;
use core\items\box\BoostKey;
use core\items\box\CommonKey;
use core\items\box\FortuneKey;
use core\items\box\LegendaryKey;
use core\items\box\MythicalKey;
use core\items\box\RareKey;
use core\items\crops\BerryBlack;
use core\items\crops\BerryBlue;
use core\items\crops\BerryPink;
use core\items\crops\BerryYellow;
use core\items\crops\Raisin;
use core\Main;
use core\managers\box\BoxManager;
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

class OpenBox extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'open_box', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {

        if (!isset($args[0])) return;

        if (!in_array($args[0], ["common", "rare", "legendary", "mythical", "black_gold", "boost", "fortune"])) return;

        $keyTarget = match ($args[0]) {
            "common" => CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON),
            "rare" => CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE),
            "boost" => CustomiesItemFactory::getInstance()->get(Ids::KEY_BOOST),
            "legendary" => CustomiesItemFactory::getInstance()->get(Ids::KEY_LEGENDARY),
            "mythical" => CustomiesItemFactory::getInstance()->get(Ids::KEY_MYTHICAL),
            "black_gold" => CustomiesItemFactory::getInstance()->get(Ids::KEY_BLACK_KEY),
            "fortune" => CustomiesItemFactory::getInstance()->get(Ids::KEY_FORTUNE),
            default => "404"
        };

        if ($keyTarget === "404") return;


        $itemInHand = $sender->getInventory()->getItemInHand();
        if ($itemInHand->getTypeId() !== $keyTarget->getTypeId()) {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§cVeuillez tenir vos clés dans votre main."));
            return;
        }





        $sender->sendForm(new MenuForm("OPEN_BOX", "Oh te voila " . $sender->getName() . " ! C'est l'heure d'ouvrir une box ! Choisit l'option qui te convient le mieux", [
            new Button("one_key"),
            new Button("three_key"),
            new Button("all_key")
        ], function (Player $player, Button $button) use ($keyTarget, $itemInHand) : void {

            $count = match ($button->getText()) {
                "one_key" => 1,
                "three_key" => 3,
                "all_key" => "all"
            };




            if ($count === "all") {
                $itemInHand = $player->getInventory()->getItemInHand();
                if ($itemInHand->getTypeId() !== $keyTarget->getTypeId()) {
                    $player->sendErrorSound();
                    $player->sendMessage(Messages::message("§cVeuillez tenir vos clés dans votre main."));
                    return;
                }

                $boxType = match ($itemInHand::class) {
                    CommonKey::class => BoxManager::COMMON,
                    RareKey::class => BoxManager::RARE,
                    LegendaryKey::class => BoxManager::LEGENDARY,
                    MythicalKey::class => BoxManager::MYTHICAL,
                    BoxBlackGold::class => BoxManager::BLACK_GOLD,
                    BoostKey::class => BoxManager::BOOST,
                    FortuneKey::class => BoxManager::FORTUNE,
                    default => null,
                };

                if (is_null($boxType)) return;


                $count = $itemInHand->getCount();
                $player->getInventory()->removeItem($itemInHand);



                $itemsBox = Main::getInstance()->getBoxManager()->getItemsWithBox($boxType);
                $items = array_values($itemsBox);


                while ($count !== 0) {
                    $count--;
                    $chance = mt_rand(0, 1500);
                    $selectedItem = $items[0];
                    foreach ($itemsBox as $itemChance => $item) {
                        if ($itemChance >= $chance) {
                            $selectedItem = $item;
                        }
                    }

                    Main::getInstance()->jobsStorage->addItemInStorage($player, $selectedItem);
                }
                Main::getInstance()->jobsStorage->saveUserCache($player);
                $player->sendMessage(Messages::message("§fVotre récompense a été ajoutée dans votre §6inventaire§f de récompenses. §l§6/reward"));
                $player->sendSuccessSound();
                return;
            }

            if ($count === 3) {
                $itemInHand = $player->getInventory()->getItemInHand();
                if ($itemInHand->getTypeId() !== $keyTarget->getTypeId()) {
                    $player->sendErrorSound();
                    $player->sendMessage(Messages::message("§cVeuillez tenir vos clés dans votre main."));
                    return;
                }

                $boxType = match ($itemInHand::class) {
                    CommonKey::class => BoxManager::COMMON,
                    RareKey::class => BoxManager::RARE,
                    LegendaryKey::class => BoxManager::LEGENDARY,
                    MythicalKey::class => BoxManager::MYTHICAL,
                    BoxBlackGold::class => BoxManager::BLACK_GOLD,
                    BoostKey::class => BoxManager::BOOST,
                    FortuneKey::class => BoxManager::FORTUNE
                };


                $count = $itemInHand->getCount();
                if ($count < 3) {
                    $player->sendErrorSound();
                    $player->sendMessage(Messages::message("§cVous n'avez pas assez de clés."));
                    return;
                }

                $player->getInventory()->removeItem($itemInHand);



                $itemsBox = Main::getInstance()->getBoxManager()->getItemsWithBox($boxType);
                $items = array_values($itemsBox);


                while ($count !== 0) {
                    $count--;
                    $chance = mt_rand(0, 1500);
                    $selectedItem = $items[0];
                    foreach ($itemsBox as $itemChance => $item) {
                        if ($itemChance >= $chance) {
                            $selectedItem = $item;
                        }
                    }

                    Main::getInstance()->jobsStorage->addItemInStorage($player, $selectedItem);
                }
                Main::getInstance()->jobsStorage->saveUserCache($player);
                $player->sendMessage(Messages::message("§fVotre récompense a été ajoutée dans votre §6inventaire§f de récompenses. §l§6/reward"));
                $player->sendSuccessSound();
                return;
            }

            $itemInHand = $player->getInventory()->getItemInHand();
            if ($itemInHand->getTypeId() !== $keyTarget->getTypeId()) {
                $player->sendErrorSound();
                $player->sendMessage(Messages::message("§cVeuillez tenir vos clés dans votre main."));
                return;
            }

            $boxType = match ($itemInHand::class) {
                CommonKey::class => BoxManager::COMMON,
                RareKey::class => BoxManager::RARE,
                LegendaryKey::class => BoxManager::LEGENDARY,
                MythicalKey::class => BoxManager::MYTHICAL,
                BoxBlackGold::class => BoxManager::BLACK_GOLD,
                BoostKey::class => BoxManager::BOOST,
                FortuneKey::class => BoxManager::FORTUNE
            };

            Cinematics::sendDoorOpenCinematic($player, $boxType, $itemInHand);
        }));
    }
}