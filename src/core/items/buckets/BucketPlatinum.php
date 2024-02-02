<?php

namespace core\items\buckets;

use core\Main;
use core\messages\Messages;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\Transparent;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\item\Bucket;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\BucketEmptyWaterSound;
use pocketmine\world\sound\BucketFillWaterSound;

class BucketPlatinum extends Bucket implements ItemComponents
{
    use ItemComponentsTrait;

    const MAX_SOURCE = 50;


    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Seau en platine remplie d'eau";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('bucket_platinum_water', $inventory);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le seau en platine a été forgé par\nle maître forgeron nommé OneUp. Son coup\nde marteau a permis de créer un seau\npresque infini.",
            "§6---",
            "§l§eStockage: §r§f" . self::MAX_SOURCE . "§6/§f" . self::MAX_SOURCE . " sources d'eau",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }


    use SoundTrait;

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $block = $player->getTargetBlock(5);
        if (!is_null($block)) {
            $this->onInteractBlock($player, $block, $block, 0, $player->getDirectionVector(), $returnedItems);
        }
        return parent::onClickAir($player, $directionVector, $returnedItems);
    }


    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
    {
        if (!isset(BucketEmptyCopper::$cooldown[$player->getXuid()])) BucketEmptyCopper::$cooldown[$player->getXuid()] = time();
        if (BucketEmptyCopper::$cooldown[$player->getXuid()] > time()) return ItemUseResult::FAIL();
        BucketEmptyCopper::$cooldown[$player->getXuid()] = time() + 0.5;

        if (Main::getInstance()->getAreaManager()->isInArea($blockClicked->getPosition())) return ItemUseResult::FAIL();


        if ($this->getNamedTag()->getInt("source", -100) == -100) {
            $this->getNamedTag()->setInt("source", self::MAX_SOURCE);
        }

        if ($this->getNamedTag()->getString('type', 'none') === 'none') {
            $this->getNamedTag()->setString('type', 'water');
        }

        $source = $this->getNamedTag()->getInt("source", 0);

        if ($blockReplace instanceof Water && $blockReplace->isSource()) {
            if ($source < self::MAX_SOURCE) {
                $this->getNamedTag()->setInt("source", $source + 1);
                $player->getWorld()->setBlock($blockReplace->getPosition(), VanillaBlocks::AIR());
                $player->getWorld()->addSound($player->getPosition(), new BucketFillWaterSound());
                $this->setLoree($source + 1);
                $player->getInventory()->setItemInHand($this);
                return ItemUseResult::SUCCESS();
            } else {
                $player->sendMessage(Messages::message("§cVous avez atteint le maximum de sources d'eau dans votre seau."));
                $this->sendErrorSound($player);
                return ItemUseResult::FAIL();
            }
        } else {
            if ($source - 1 > 0) {
                $this->getNamedTag()->setInt("source", $source - 1);
                $player->getWorld()->setBlock($blockReplace->getPosition(), VanillaBlocks::WATER());
                $this->setLoree($source - 1);
                $player->getInventory()->setItemInHand($this);
                $player->getWorld()->addSound($player->getPosition(), new BucketEmptyWaterSound());
            } else {
                $this->getNamedTag()->setInt("source", 0);
                $this->setLoree(0);
                $player->getInventory()->setItemInHand(CustomiesItemFactory::getInstance()->get(Ids::BUCKET_PLATINUM_EMPTY)->setNamedTag($this->getNamedTag()));
                $player->getWorld()->setBlock($blockReplace->getPosition(), VanillaBlocks::WATER());
                $player->getWorld()->addSound($player->getPosition(), new BucketEmptyWaterSound());
            }
        }

        return ItemUseResult::NONE();
    }

    public function setLoree(int $source): Item
    {
        return parent::setLore([
            "§6---",
            "§l§eDescription:§r§f Le seau en platinum a été forgé par\nle maître forgeron nommé OneUp. Son coup\nde marteau a permis de créer un seau\npresque infini.",
            "§6---",
            "§l§eStockage: §r§f{$source}§6/§f" . self::MAX_SOURCE . " sources §rd'eau",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }
}