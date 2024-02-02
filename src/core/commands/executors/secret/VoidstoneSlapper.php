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
use core\items\tools\VoidStone;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\BlockIds;
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

class VoidstoneSlapper extends Executor
{
    use SoundTrait;

    public function __construct(string $name = '1234567891_voidstone', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $sender->sendForm(new MenuForm("VOIDSTONE", "Besoin de matos ? J'ai ce qu'il te faut ! J'ai juste besoin de pierre taillée / pierre des abymes :)", [
            new Button("bottle"),
            new Button("bottle_empty"),
            new Button("platine"),
            new Button("amethyst"),
            new Button("emerald"),
            new Button("nugget"),
        ], function (Player $player, Button $button): void {
            $cobble = match ($button->getText()) {
                "bottle" => 100000,
                "bottle_empty" => 50000,
                "platine" => 250000,
                "amethyst" => 150000,
                "emerald" => 75000,
                "nugget" => 1000000,
            };

            $item = match ($button->getText()) {
                "bottle" => CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_JOBS),
                "bottle_empty" => CustomiesItemFactory::getInstance()->get(Ids::EMPTY_BOTTLE, 256),
                "platine" => CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK, 16),
                "amethyst" => CustomiesItemFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK, 16),
                "emerald" => CustomiesItemFactory::getInstance()->get(BlockIds::EMERALD_BLOCK, 16),
                "nugget" => CustomiesItemFactory::getInstance()->get(Ids::GOLD_NUGGET, 3),
            };


            $voidstone = null;
            $slotVoidstone = 0;
            foreach ($player->getInventory()->getContents() as $slot => $itemCheck) {
                if ($itemCheck instanceof VoidStone) {
                    $voidstone = $itemCheck;
                    $slotVoidstone = $slot;
                    break;
                }
            }

            if (is_null($voidstone)) {
                $player->sendErrorSound();
                $player->sendMessage(Messages::message("§cVous n'avez pas de voidstone dans votre inventaire."));
                return;
            }

            $cobbleCount = $voidstone->getCobbleCount();
            $deepslateCount = $voidstone->getDeepslateCount();


            if (($cobbleCount + $deepslateCount) < $cobble) {
                $player->sendErrorSound();
                $player->sendMessage(Messages::message("§cVous n'avez pas assez de pierres taillées / pierres des abymes dans votre voidstone."));
                return;
            }

            if (!$player->getInventory()->canAddItem($item)) {
                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                $player->sendErrorSound();
                return;
            }

            if (($cobbleCount - $cobble) <= 0) {
                $cobble -= $cobbleCount;
                $cobbleCount  = 0;
            } else {
                $cobbleCount -= $cobble;
            }


            if (($deepslateCount - $cobble) <= 0) {
                $cobble -= $deepslateCount;
                $deepslateCount  = 0;
            } else {
                $deepslateCount -= $cobble;
            }


            $voidstone->setCobble($cobbleCount);
            $voidstone->setDeepslate($deepslateCount);
            $player->getInventory()->setItem($slotVoidstone, $voidstone);
            $player->getInventory()->addItem($item);
            $player->sendSuccessSound();

            $player->sendMessage("§7[Vagabon]§6 :§f Merci de ton échange !");
        }));
    }
}