<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace core\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\CraftingGrid;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;

final class CustomCraftingTableInventory extends CraftingGrid implements BlockInventory, TemporaryInventory{
    use BlockInventoryTrait;

    public function __construct(Position $holder){
        $this->holder = $holder;
        parent::__construct(CraftingGrid::SIZE_BIG);
    }

    public function onClose(Player $who): void
    {
        $vector = $who->getPosition()->add(0, 2, 0);
        $blockTranslator = TypeConverter::getInstance()->getBlockTranslator();

        $packet = UpdateBlockPacket::create(
            BlockPosition::fromVector3($vector->add(0, 1, 0)),
            $blockTranslator->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId()),
            UpdateBlockPacket::FLAG_NOGRAPHIC,
            UpdateBlockPacket::DATA_LAYER_NORMAL
        );


        $who->getNetworkSession()->sendDataPacket($packet);
        parent::onClose($who);
    }
}
