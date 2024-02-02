<?php

namespace core\items\others;

use core\Main;
use core\messages\Messages;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class MoneyBag extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Sac d'argent";

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('sac_money', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Un sac d'argent prêt à être utilisé\npour améliorer vos enchantements ou échanger contre\nde nouvelles compétences.",
            "§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $money = 10000;
        Main::getInstance()->getEconomyManager()->addMoney($player, $money);
        $player->sendMessage(Messages::message("§aVous avez reçu §e" . $money . "§a$"));
        $this->pop();
        return parent::onClickAir($player, $directionVector, $returnedItems);
    }
}