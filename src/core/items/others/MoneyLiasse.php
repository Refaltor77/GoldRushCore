<?php

namespace core\items\others;

use core\Main;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class MoneyLiasse extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Liasse de 1000 §6$";

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('money', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une liasse de billets d'une valeur de 1000 $,\nutilisable dans les commerces les moins recommandables.",
            "§6---",
            "§l§eUtilisation: §r§7Click droit pour obtenir §61000$",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }

    use SoundTrait;

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $money = 1000;
        Main::getInstance()->getEconomyManager()->addMoney($player, $money);

        $player->sendMessage("§r[§a+§r] §a1000$");
        $this->sendSuccessSound($player);
        $this->pop();
        return parent::onClickAir($player, $directionVector, $returnedItems);
    }
}