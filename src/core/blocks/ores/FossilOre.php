<?php

namespace core\blocks\ores;

use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\player\Player;

class FossilOre extends Opaque
{

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            Main::getInstance()->getTopLuckManager()->addOre($player);
            Main::getInstance()->getJobsManager()->addXp($player, JobsManager::MINOR, 25);
            Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), 25, JobsManager::MINOR);
        }
        return parent::onBreak($item, $player, $returnedItems);
    }

    public function getDropsForCompatibleTool(Item $item) : array{

        $fossils = [
            "Diplodocus" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_DIPLODOCUS),
                "chance" => 20, // Par exemple, 10% de chance
            ],
            "Nodosaurus" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_NODOSAURUS),
                "chance" => 30, // Par exemple, 20% de chance
            ],
            "Pterodactyle" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_PTERODACTYLE),
                "chance" => 20, // Par exemple, 15% de chance
            ],
            "Fossile" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL),
                "chance" => 50, // Par exemple, 10% de chance
            ],
            "Brachiosaurus" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_BRACHIOSAURUS),
                "chance" => 20, // Par exemple, 5% de chance
            ],
            "Spinosaure" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_SPINOSAURE),
                "chance" => 10, // Par exemple, 5% de chance
            ],
            "Stegosaurus" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_STEGOSAURUS),
                "chance" => 10, // Par exemple, 5% de chance
            ],
            "Triceratops" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TRICERATOPS),
                "chance" => 5, // Par exemple, 5% de chance
            ],
            "Tyrannosaure" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TYRANNOSAURE),
                "chance" => 1, // Par exemple, 5% de chance
            ],
            "Velociraptor" => [
                "item" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_VELOCIRAPTOR),
                "chance" => 5, // Par exemple, 5% de chance
            ],
        ];

        $chance = mt_rand(0, 100);
        $fossil = CustomiesItemFactory::getInstance()->get(Ids::FOSSIL);

        foreach ($fossils as $fossilName => $fossilData) {
            if ($chance < $fossilData["chance"]) {
                $fossil = $fossilData["item"];
                break; // Sortez de la boucle dès qu'un fossile est attribué
            }
            $chance -= $fossilData["chance"]; // Réduisez la chance pour le prochain fossile
        }


        return [
            $fossil
        ];
    }

    public function isAffectedBySilkTouch() : bool{
        return false;
    }

    protected function getXpDropAmount() : int{
        return mt_rand(0, 2);
    }
}