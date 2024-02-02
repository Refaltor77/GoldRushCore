<?php

namespace core\blocks\blocks;

use core\blocks\tiles\MobSpawnerTile;
use core\entities\vanilla\Chicken;
use core\entities\vanilla\Cow;
use core\entities\vanilla\Creeper;
use core\entities\vanilla\Enderman;
use core\entities\vanilla\Mouton;
use core\entities\vanilla\Pig;
use core\entities\vanilla\Skeleton;
use core\entities\vanilla\ZombieCustom;
use core\items\egg\EggChicken;
use core\items\egg\EggCow;
use core\items\egg\EggCreeper;
use core\items\egg\EggEnderman;
use core\items\egg\EggMouton;
use core\items\egg\EggPig;
use core\items\egg\EggSkeleton;
use core\items\egg\EggZombie;
use core\items\tools\PickaxeSpawner;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\block\Transparent;
use pocketmine\entity\Location;
use pocketmine\entity\Zombie;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class MonsterSpawner extends \pocketmine\block\MonsterSpawner
{
    public function isAffectedBySilkTouch(): bool{
        return true;
    }


    public function readStateFromWorld(): Block
    {
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if ($tile instanceof MobSpawnerTile) {
            $tile->setEntityId($tile->getEntityId());
        }
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(),1);
        return parent::readStateFromWorld();
    }

    public function getSilkTouchDrops(Item $item): array{
        return [];
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (is_null($tile)) {
            $this->getPosition()->getWorld()->addTile(new MobSpawnerTile($this->getPosition()->getWorld(), $this->getPosition()));
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }


    public static array $cooldownActions = [];
    use SoundTrait;


    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof MobSpawnerTile) {
            if (!is_null($tile)) $this->getPosition()->getWorld()->removeTile($tile);
            $this->getPosition()->getWorld()->addTile(new MobSpawnerTile($this->getPosition()->getWorld(), $this->getPosition()));
            $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        }
        if (!$player instanceof Player) return false;



        if (!isset(self::$cooldownActions[$player->getXuid()])) {
            self::$cooldownActions[$player->getXuid()] = time();
        }

        if (self::$cooldownActions[$player->getXuid()] >= time()) return false;
        self::$cooldownActions[$player->getXuid()] = time() + 1;



        $entityId = "";
        switch ($item::class) {
            case EggCreeper::class:
                $entityId = EntityIds::CREEPER;
                break;
            case EggSkeleton::class:
                $entityId = EntityIds::SKELETON;
                break;
            case EggChicken::class:
                $entityId = EntityIds::CHICKEN;
                break;
            case EggPig::class:
                $entityId = EntityIds::PIG;
                break;
            case EggZombie::class:
                $entityId = EntityIds::ZOMBIE;
                break;
            case EggCow::class:
                $entityId = EntityIds::COW;
                break;
            case EggEnderman::class:
                $entityId = EntityIds::ENDERMAN;
                break;
            case EggMouton::class:
                $entityId = EntityIds::SHEEP;
                break;
            default:
                $player->sendMessage(Messages::message("§cVous n'avez pas d'oeuf dans votre main."));
                $this->sendErrorSound($player);
                return false;
        }


        if ($tile->getEntityId() !== "") {
            $player->sendMessage(Messages::message("§cVous ne pouvez pas changer d'entité dans le spawner."));
            $this->sendErrorSound($player);
            return false;
        }
        $tile->setEntityId($entityId);
        $this->position->getWorld()->setBlock($this->position, $this);
        $this->sendSuccessSound($player);
        if(!$player->isCreative()) {
            $player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setCount($player->getInventory()->getItemInHand()->getCount() - 1));
        }
        return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
    }

    public function getDrops(Item $item): array
    {

        if ($item instanceof PickaxeSpawner) {
            $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
            return [
                $this->asItem(),
                match ($tile->getEntityId()){
                    EntityIds::CREEPER => CustomiesItemFactory::getInstance()->get(Ids::EGG_CREEPER),
                    EntityIds::SKELETON => CustomiesItemFactory::getInstance()->get(Ids::EGG_SKELETON),
                    EntityIds::CHICKEN => CustomiesItemFactory::getInstance()->get(Ids::EGG_CHICKEN),
                    EntityIds::PIG => CustomiesItemFactory::getInstance()->get(Ids::EGG_COCHON),
                    EntityIds::ZOMBIE => CustomiesItemFactory::getInstance()->get(Ids::EGG_ZOMBIE),
                    EntityIds::COW => CustomiesItemFactory::getInstance()->get(Ids::EGG_COW),
                    EntityIds::ENDERMAN => CustomiesItemFactory::getInstance()->get(Ids::EGG_ENDERMAN),
                    EntityIds::SHEEP => CustomiesItemFactory::getInstance()->get(Ids::EGG_MUTTON),
                    default => VanillaItems::AIR()
                }
            ];
        } else return [];
    }

    public function onScheduledUpdate(): void{

        $class = [
            EntityIds::COW => Cow::class,
            EntityIds::CREEPER => Creeper::class,
            EntityIds::SKELETON => Skeleton::class,
            EntityIds::PIG => Pig::class,
            EntityIds::CHICKEN => Chicken::class,
            EntityIds::ZOMBIE => ZombieCustom::class,
            EntityIds::ENDERMAN => Enderman::class,
            EntityIds::SHEEP => Mouton::class
        ];



        $tile = $this->position->getWorld()->getTile($this->position);

        if(!$tile instanceof MobSpawnerTile || $tile->entityInfo === null){
            return;
        }

        if($tile->getTick() > 0) $tile->decreaseTick();
        if($tile->isValidEntity() && $tile->canEntityGenerate() && $tile->getTick() <= 0){
            $tile->setTick(20 * 18);
            for($i = 0; $i < 3; $i++){
                $x = 2;
                $z = 2;
                $pos = $tile->getPosition();
                $pos = new Location($pos->x + $x, $pos->y + 1, $pos->z + $z, $pos->getWorld(), 0, 0);
                if (isset($class[$tile->getEntityId()])) {
                    $entity = new $class[$tile->getEntityId()]($pos);
                    $entity->spawnToAll();
                }
            }
        }
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }
}