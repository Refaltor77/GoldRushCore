<?php

namespace core\blocks\crops;

use core\blocks\BlockHistoryData;
use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\GlowBerriesPickSound;

class FlowerPercent extends CustomCropsNoDirtFertil
{

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
    }


    public function ticksRandomly(): bool{return false;}


    public function onScheduledUpdate(): void
    {

    }



    public function getDrops(Item $item): array
    {
        return [
           CustomiesItemFactory::getInstance()->get(Ids::FLOWER_PERCENT)
        ];
    }
}