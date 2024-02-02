<?php

namespace core\items\egg;

use core\blocks\blocks\MonsterSpawner;
use core\entities\vanilla\Pig;
use core\messages\Messages;
use core\traits\SoundTrait;
use customiesdevs\customies\item\component\CooldownComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

class EggPig extends Item implements ItemComponents
{
    use ItemComponentsTrait;
    use SoundTrait;


    public static array $cooldownAction = [];

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Oeuf de cochon';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_NATURE,
            CreativeInventoryInfo::GROUP_MOB_EGGS,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('egg_cochon', $inventory);
        $this->addComponent(new CooldownComponent($this->getName(), 1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Oeuf de cochon qui permet de faire spawn un cochon\nvous pouvez utiliser cette oeuf\nsur un spawner",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
    {
        if ($blockClicked instanceof MonsterSpawner) return ItemUseResult::NONE();

        if (!isset(self::$cooldownAction[$player->getXuid()])) {
            self::$cooldownAction[$player->getXuid()] = time();
        }
        if (self::$cooldownAction[$player->getXuid()] > time()) return ItemUseResult::NONE();
        self::$cooldownAction[$player->getXuid()] = time() + 1;

        $this->sendErrorSound($player);
        $player->sendMessage(Messages::message("§cVous ne pouvez pas placer une entité avec votre œuf. Tapez un spawner pour l'activer !"));
        return ItemUseResult::SUCCESS();
    }

    protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
    {
        return new Pig(new Location($pos->getX(), $pos->getY(), $pos->getZ(), $world, $yaw, $pitch));
    }
}