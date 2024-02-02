<?php

namespace core\blocks;

use customiesdevs\customies\block\permutations\Permutable;
use customiesdevs\customies\block\permutations\RotatableTrait;
use pocketmine\block\Transparent;

class TransparentPermutable extends Transparent implements Permutable
{
    use RotatableTrait;
}