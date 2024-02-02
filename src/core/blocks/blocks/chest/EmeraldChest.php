<?php

namespace core\blocks\blocks\chest;

use core\api\gui\ChestInventory;
use core\blocks\DurabilityObsidian;
use core\blocks\tiles\EmeraldChestTile;
use core\cooldown\BasicCooldown;
use core\entities\dynamites\AmethystDynamite;
use core\entities\dynamites\BaseDynamite;
use core\entities\dynamites\EmeraldDynamite;
use core\entities\dynamites\PlatineDynamite;
use core\entities\dynamites\WaterDynamite;
use core\inventory\KeypadInventory;
use core\items\others\keypad\Keypad;
use core\Main;
use core\messages\Messages;
use core\settings\BlockIds;
use core\settings\Ids;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\block\permutations\Permutable;
use customiesdevs\customies\block\permutations\RotatableTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\utils\SupportType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\BlockBreakSound;

class EmeraldChest extends Chest implements Permutable
{
    use RotatableTrait;

    /**
     * @return AxisAlignedBB[]
     */
    protected function recalculateCollisionBoxes() : array{
        //these are slightly bigger than in PC
        return [AxisAlignedBB::one()->contract(0.025, 0, 0.025)->trim(Facing::UP, 0.05)];
    }

    public function getSupportType(int $facing) : SupportType{
        return SupportType::NONE();
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $place =  parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);

        if ($place) {
            Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->setBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ(),
                new DurabilityObsidian(15)
            );
        }

        return true;
    }


    public function showDuraPopup(Position $pos, $name, Entity $entityExplode): void
    {


    }

    public function onExplode(Entity $entity): void {

        if (in_array($entity::class, [
            BaseDynamite::class,
            EmeraldDynamite::class,
            AmethystDynamite::class,
            PlatineDynamite::class,
            WaterDynamite::class
        ]))


            $data =   Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ()
            );

        if (!$data instanceof DurabilityObsidian) {
            Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->setBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ(),
                new DurabilityObsidian(15)
            );


            $data =   Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ()
            );
        }


        if (!is_null($data)) {
            $durability = (int)$data->getDurability();
            if ($durability <= 0) {
                $this->getPosition()->getWorld()->addSound($this->getPosition(), new BlockBreakSound($this));
                $this->getPosition()->getWorld()->addParticle($this->getPosition(), new BlockBreakParticle($this));
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), VanillaBlocks::AIR());
            } else {
                $moin = 1;
                if ($entity instanceof EmeraldDynamite) $moin = 2;
                if ($entity instanceof AmethystDynamite) $moin = 4;
                if ($entity instanceof PlatineDynamite) $moin = 6;
                if ($entity instanceof WaterDynamite) return;
                $data->setDurability($durability - $moin);
                $this->showDuraPopup($this->getPosition(), "§lDurability: §r§f". $data->getDurability() . "§l§6/§r§f8", $entity);
            }
        }
    }


    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        if (!BasicCooldown::validChest($player)) return false;

        if($player instanceof Player){
            $chest = $this->position->getWorld()->getTile($this->position);
            if($chest instanceof EmeraldChestTile){
                if ($chest->isLocked) {
                    $code = $chest->getCode();
                    $keypadInv = new KeypadInventory();
                    $pos = $this->getPosition();


                    if ($chest->authorXuid == $player->getXuid() && $player->isSneaking()) {
                        $keypadInv = new KeypadInventory();
                        $keypadInv->setAcceptCallback(function (string $codeInput) use ($player, $code, $pos): void {
                            if ($player->isConnected()) {
                                $chest = $pos->getWorld()->getTile($pos);
                                if ($chest instanceof EmeraldChestTile) {
                                    $chest->addAuthorized($player);
                                    $chest->setLocked($codeInput);
                                    $player->setCurrentWindow($chest->getInventory());
                                    $player->sendSuccessSound();
                                } else {
                                    $player->removeCurrentWindow();
                                    $player->sendErrorSound();
                                    $player->sendMessage(Messages::message("§cLe coffre n'existe plus."));
                                }
                            }
                        });
                        $player->setCurrentWindow($keypadInv);
                        return true;
                    }




                    if ($chest->isAuthorized($player) || $chest->authorXuid == $player->getXuid()) {
                        $player->setCurrentWindow($chest->getInventory());
                        return true;
                    }

                    $keypadInv->setAcceptCallback(function (string $codeInput) use ($player, $code, $pos): void {
                        if ($player->isConnected()) {
                            if ($codeInput == $code) {
                                $chest = $pos->getWorld()->getTile($pos);
                                if ($chest instanceof EmeraldChestTile) {
                                    $player->setCurrentWindow($chest->getInventory());
                                    $player->sendSuccessSound();
                                } else {
                                    $player->removeCurrentWindow();
                                    $player->sendErrorSound();
                                    $player->sendMessage(Messages::message("§cLe coffre n'existe plus."));
                                }
                            } else {
                                $ev = new EntityDamageEvent($player, 500, 1);
                                $player->attack($ev);
                                $player->sendMessage(Messages::message("§cLe mot de passe est incorrect"));
                                $player->removeCurrentWindow();
                                $player->sendErrorSound();
                            }
                        }
                    });
                    $player->setCurrentWindow($keypadInv);
                    return true;
                } else {

                    if ($player->getInventory()->getItemInHand() instanceof Keypad) {
                        $keypadInv = new KeypadInventory();
                        $pos = $this->getPosition();
                        $keypadInv->setAcceptCallback(function (string $codeInput) use ($player, $pos): void {
                            if ($player->isConnected()) {
                                $chest = $pos->getWorld()->getTile($pos);
                                if ($chest instanceof EmeraldChestTile) {

                                    if (!$player->getInventory()->contains(CustomiesItemFactory::getInstance()->get(Ids::KEYPAD))) {
                                        $player->sendErrorSound();
                                        $player->sendMessage(Messages::message("§cVous n'avez plus votre keypad."));
                                        return;
                                    }

                                    $chest->setAuthor($player);
                                    $player->getInventory()->remove(CustomiesItemFactory::getInstance()->get(Ids::KEYPAD));
                                    $chest->setLocked($codeInput);
                                    $pos->getWorld()->setBlock($pos, CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_CHEST_LOCK));
                                    $player->setCurrentWindow($chest->getInventory());
                                    $player->sendSuccessSound();
                                } else {
                                    $player->removeCurrentWindow();
                                    $player->sendErrorSound();
                                    $player->sendMessage(Messages::message("§cLe coffre n'existe plus."));
                                }
                            }
                        });
                        $player->sendSuccessSound();
                        $player->setCurrentWindow($keypadInv);
                        return true;
                    } else {
                        $player->setCurrentWindow($chest->getInventory());
                        return true;
                    }
                }
            }
        }

        return true;
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        $chest = $this->position->getWorld()->getTile($this->position);
        if ($chest instanceof EmeraldChestTile) {
            if ($chest->isLocked) {
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::KEYPAD));
            }
        }
        return parent::onBreak($item, $player, $returnedItems);
    }

    public function getFuelTime() : int{
        return 300;
    }
}