<?php

namespace core\entities;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;

class Test extends Living
{

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1, 1, 0.5);
    }

    public static function getNetworkTypeId(): string
    {
        return "goldrush:example";
    }

    public function getName(): string
    {
        return "test";
    }
}