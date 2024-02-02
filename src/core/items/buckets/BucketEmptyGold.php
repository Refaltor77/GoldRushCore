<?php

namespace core\items\buckets;

use core\Main;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\Lava;
use pocketmine\block\Liquid;
use pocketmine\block\Transparent;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\item\Bucket;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\BucketEmptyWaterSound;

class BucketEmptyGold extends Bucket implements ItemComponents
{
    use ItemComponentsTrait;


    const MAX_SOURCE = 100;

    public static array $cooldown = [];


    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Seau en or';

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('bucket_empty_gold', $inventory);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le seau en or a été forgé par\nle maître forgeron nommé OneUp. Son coup\nde marteau a permis de créer un seau\npresque infini.",
            "§6---",
            "§l§eStockage: §r§f0§6/§f" . self::MAX_SOURCE . " §rsources",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
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
        if (!isset(self::$cooldown[$player->getXuid()])) self::$cooldown[$player->getXuid()] = time();
        if (self::$cooldown[$player->getXuid()] > time()) return ItemUseResult::FAIL();
        self::$cooldown[$player->getXuid()] = time() + 0.5;

        if (Main::getInstance()->getAreaManager()->isInArea($blockClicked->getPosition())) return ItemUseResult::FAIL();


        if ($this->getNamedTag()->getInt("source", 404) === 404) {
            $this->getNamedTag()->setInt("source", 0);
        }

        if ($this->getNamedTag()->getString('type', 'none') === 'none') {
            $this->getNamedTag()->setString('type', 'none');
        }

        if ($blockReplace instanceof Liquid && $blockReplace->isSource()) {
            if ($blockReplace instanceof Water || $blockReplace instanceof Lava) {
                $this->getNamedTag()->setInt("source", 1);
                $this->getNamedTag()->setString('type', $blockReplace instanceof Water ? 'water' : 'lava');

                $this->setLore([
                    "§6---",
                    "§l§eDescription:§r§f Le seau en platinum a été forgé par\nle maître forgeron nommé OneUp. Son coup\nde marteau a permis de créer un seau\npresque infini.",
                    "§6---",
                    "§l§eStockage: §r§f{$this->getNamedTag()->getInt("source")}§6/§f" . self::MAX_SOURCE . " sources §r" . ($this->getNamedTag()->getString('type') === 'lava' ? 'de laves' : "d'eau"),
                    "§6---",
                    "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
                ]);

                $player->getInventory()->setItemInHand(CustomiesItemFactory::getInstance()->get($this->getNamedTag()->getString("type") === "water" ? Ids::BUCKET_GOLD_WATER : Ids::BUCKET_GOLD_LAVA)->setNamedTag($this->getNamedTag()));
                $player->getWorld()->setBlock($blockReplace->getPosition(), VanillaBlocks::AIR());
                $player->getWorld()->addSound($blockReplace->getPosition()->add(0.5, 0.5, 0.5), new BucketEmptyWaterSound());


                return ItemUseResult::SUCCESS();
            }
        }

        return ItemUseResult::NONE();
    }
}