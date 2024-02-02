<?php

declare(strict_types=1);

namespace core\blocks\blocks\biomes\spectral\three;

use customiesdevs\customies\block\CustomiesBlockFactory;
use czechpmdevs\multiworld\libs\muqsit\vanillagenerator\generator\object\tree\GenericTree;
use czechpmdevs\multiworld\libs\muqsit\vanillagenerator\generator\object\tree\GenericTreeSpectral;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;

class SpectraThreeGeneric extends GenericTreeSpectral {

    /**
     * Initializes this tree with a random height, preparing it to attempt to generate.
     *
     * @param Random $random the PRNG
     * @param BlockTransaction $transaction the BlockTransaction used to check for space and to fill wood and leaf
     */
    public function __construct(Random $random, BlockTransaction $transaction) {
        parent::__construct($random, $transaction);
        $this->setHeight($random->nextBoundedInt(3) + 5);
        $this->setType(CustomiesBlockFactory::getInstance()->get("goldrush:spectral_log"), CustomiesBlockFactory::getInstance()->get("goldrush:spectral_leaves"));
    }
}