<?php


namespace core\blocks\vanilla;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Input;
use core\api\form\MenuForm;
use core\messages\Messages;
use core\traits\SoundTrait;
use pocketmine\block\Anvil;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilUseSound;

class AnvilCustom extends Anvil
{
    use SoundTrait;

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof Player) {
            $player->sendForm(new MenuForm("ANVIL", "Repare ton item", [
                new Button("Reparer"),
                new Button("Rename")
            ], function (Player $player, Button $button): void {
                switch ($button->getValue()) {
                    case 0:
                        if ($player->getInventory()->getItemInHand()->isNull()) {
                            $player->sendMessage(Messages::message("§cVous ne pouvez pas réparer de l'air."));
                            $this->sendErrorSound($player);
                            return;
                        }

                        if (!$player->getInventory()->getItemInHand() instanceof Durable) {
                            $player->sendMessage(Messages::message("§cSeuls les items avec une durabilité ont réparable."));
                            $this->sendErrorSound($player);
                            return;
                        }

                        if ($player->getXpManager()->getXpLevel() < 10) {
                            $player->sendMessage(Messages::message("§cLe coût de réparation vaux 10 niveaux d 'xp."));
                            $this->sendErrorSound($player);
                            return;
                        }


                        /** @var Durable $hand */
                        $hand = $player->getInventory()->getItemInHand();
                        if ($hand->getDamage() === 0) {
                            $player->sendMessage(Messages::message("§cVotre item est déjà en bon état."));
                            $this->sendErrorSound($player);
                            return;
                        }

                        $item = clone $hand;
                        $item->setDamage(0);

                        $player->getXpManager()->setXpLevel($player->getXpManager()->getXpLevel() - 10);
                        $player->getInventory()->setItemInHand($item);
                        $player->getWorld()->addSound($player->getEyePos(), new AnvilUseSound());
                        break;
                    case 1:

                        if ($player->getInventory()->getItemInHand()->isNull()) {
                            $player->sendMessage(Messages::message("§cVous ne pouvez pas réparer de l'air."));
                            $this->sendErrorSound($player);
                            return;
                        }

                        if ($player->getXpManager()->getXpLevel() < 5) {
                            $player->sendMessage(Messages::message("§cLe coût du rename vaux 5 niveaux d 'xp."));
                            $this->sendErrorSound($player);
                            return;
                        }



                        $player->sendForm(new CustomForm("Renomer votre item dans votre main", [
                            new Input("Name", "DemonSword")
                        ], function (Player $player, CustomFormResponse $response): void {
                            $data = $response->getValues();

                            $name = $data[0];
                            $item = $player->getInventory()->getItemInHand()->setCustomName($name);
                            $player->getXpManager()->setXpLevel($player->getXpManager()->getXpLevel() - 5);
                            $player->getInventory()->setItemInHand($item);
                            $player->getWorld()->addSound($player->getEyePos(), new AnvilUseSound());
                        }));
                        break;
                }
            }));


            return true;
        } else return false;
    }
}