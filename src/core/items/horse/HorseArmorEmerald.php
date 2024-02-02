<?php

namespace core\items\horse;

use core\listeners\types\horse\HorseEvent;
use core\Main;
use core\messages\Messages;
use core\tasks\SpawnHorseTask;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class HorseArmorEmerald extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Monture en émeraude';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->initComponent('monture_emerald', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f La monture en émeraude, fidèle compagnon\nil vous accompagnera jusqu'au plus profond du\nmonde de Goldrush.",
            "§6---",
            "§l§eVitesse: §l1.5",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }


    public function isFireProof(): bool
    {
        return true;
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }

    public function keepOnDeath(): bool
    {
        return true;
    }


    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        if (!Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($player->getPosition())['pvp']) {
            $player->sendMessage(Messages::message("§cLe pvp est désactivé, vous ne pouvez pas sortir votre monture."));
            return ItemUseResult::FAIL();
        }
        if ($this->getNamedTag()->getString('xuid', 'none') !== $player->getXuid()) {
            $player->sendErrorSound();
            $player->sendMessage(Messages::message("§cCette monture ne vous appartient pas !"));
            return ItemUseResult::FAIL();
        }

        if (isset(HorseEvent::$playerMount[$player->getName()])) {
            return parent::onClickAir($player, $directionVector, $returnedItems);
        }
        if (isset(SpawnHorseTask::$isSpawn[$player->getXuid()])) {
            $player->sendMessage("§cVous ne pouvez pas invoquer plusieurs montures en même temps.");
            return parent::onClickAir($player, $directionVector, $returnedItems);
        }
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new SpawnHorseTask($player, 1.4, $this), 20);
        return parent::onClickAir($player, $directionVector, $returnedItems);
    }
}