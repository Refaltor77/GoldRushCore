<?php

namespace core\items\staff;

use core\Main;
use core\traits\HomeTrait;
use core\traits\UtilsTrait;
use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class RandomTp extends Item implements ItemComponents
{
    use ItemComponentsTrait;
    use UtilsTrait;
    use HomeTrait;

    public static array $cache = [];
    public static array $cooldown = [];

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Random TP';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('gunpowder', $inventory);
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Se tp a un joueur\nau hasard",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "POUAH JE TE FAIS LA RONDEL TOI QUI LIS CE TRUC"
        ]);
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
    {
        if (!Main::getInstance()->getStaffManager()->isInStaffMode($player) && !Server::getInstance()->isOp($player->getName())) return ItemUseResult::FAIL();
        $execute = true;
        if (!isset(self::$cooldown[$player->getName()])) {
            self::$cooldown[$player->getName()] = time() + 2;
        } else {
            if (self::$cooldown[$player->getName()] > time()) {
                $execute = false;
            } else {
                self::$cooldown[$player->getName()] = time() + 2;
            }
        }

        if (!$execute) return ItemUseResult::FAIL();


        $allPlayers = Server::getInstance()->getOnlinePlayers();
        unset($allPlayers[array_search($player, $allPlayers)]);
        if ($allPlayers === []) {
            $player->sendMessage("§c[§4STAFF§c] §fSnif ! Personne est connecté !");
            return ItemUseResult::FAIL();
        }

        $playerRandom = Server::getInstance()->getOnlinePlayers()[array_rand($allPlayers)];
        $player->teleport($playerRandom->getPosition());
        $player->sendSuccessSound();
        $player->sendMessage("§c[§4STAFF§c] §fTéléportation sur le joueur : §c" . $playerRandom->getName());
        return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector, $returnedItems);
    }
}