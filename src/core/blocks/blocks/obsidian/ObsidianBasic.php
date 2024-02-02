<?php

namespace core\blocks\blocks\obsidian;

use core\blocks\DurabilityObsidian;
use core\entities\dynamites\AmethystDynamite;
use core\entities\dynamites\BaseDynamite;
use core\entities\dynamites\EmeraldDynamite;
use core\entities\dynamites\PlatineDynamite;
use core\entities\dynamites\WaterDynamite;
use core\entities\XpAndDamage;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\BlockTransaction;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\BlockBreakSound;
use pocketmine\world\sound\XpCollectSound;

class ObsidianBasic extends Opaque
{
    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
    }


    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $place =  parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);

        if ($place) {
            Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->setBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ(),
                new DurabilityObsidian(5)
            );
        }

        return true;
    }


    public function showDuraPopup(Position $pos, $name, Entity $entityExplode): void
    {

    }

    public function onExplode(Entity $entity): void {

        if (in_array($entity::class, [
            BaseDynamite::class,
            EmeraldDynamite::class,
            AmethystDynamite::class,
            PlatineDynamite::class,
            WaterDynamite::class
        ]))


            $data =   Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ()
            );

        if (!$data instanceof DurabilityObsidian) {
            Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->setBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ(),
                new DurabilityObsidian(15)
            );


            $data =   Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ()
            );
        }


        if (!is_null($data)) {
            $durability = (int)$data->getDurability();
            if ($durability <= 0) {
                $this->getPosition()->getWorld()->addSound($this->getPosition(), new BlockBreakSound($this));
                $this->getPosition()->getWorld()->addParticle($this->getPosition(), new BlockBreakParticle($this));
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), VanillaBlocks::AIR());
            } else {
                $moin = 1;
                if ($entity instanceof EmeraldDynamite) $moin = 2;
                if ($entity instanceof AmethystDynamite) $moin = 4;
                if ($entity instanceof PlatineDynamite) $moin = 6;
                if ($entity instanceof WaterDynamite) return;
                $data->setDurability($durability - $moin);
                $this->showDuraPopup($this->getPosition(), "§lDurability: §r§f". $data->getDurability() . "§l§6/§r§f8", $entity);
            }
        }
    }
}