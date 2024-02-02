<?php

namespace core\entities;

use core\api\gui\ChestInventory;
use core\cinematic\Cinematics;
use core\events\LogEvent;
use core\Main;
use core\managers\box\BoxManager;
use core\messages\Messages;
use core\settings\BlockIds;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\ExplodeSound;

class BoxRare extends Entity
{
    public bool $isAnimation = false;
    public int $time = 0;
    public ChestInventory $inv;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTag("§6- §fRare" . TextFormat::GREEN . "Box §6-\n\n§7§oTape moi");

        $location->pitch = 0.0;
        $location->yaw = round($location->getYaw() / 90) * 90;
        $location->x = $location->getFloorX() + 0.5;
        $location->y = $location->getFloorY();
        $location->z = $location->getFloorZ() + 0.5;


        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
            $this->getWorld()->setBlock(new Position($this->location->getX(), $this->location->getY(), $this->location->getZ(), $this->getWorld()), VanillaBlocks::BARRIER());
        }), 20);


        $inv = new ChestInventory();
        $inv->setViewOnly(true);
        $inv->setName("rare_box");
        $inv->setContents(array_values(Main::getInstance()->getBoxManager()->getItemsWithBox(BoxManager::RARE)));
        $this->inv = $inv;
        parent::__construct($location, $nbt);
    }

    public function ajusterPitch($nombre) {
        $pitch = 90;  // Valeur par défaut

        if ($nombre >= 45 && $nombre < 135) {
            $pitch = 90;  // Ajuster le pitch à 90 si le nombre est proche de 90 degrés
        } elseif ($nombre >= 135 && $nombre < 225) {
            $pitch = 180; // Ajuster le pitch à 180 si le nombre est proche de 180 degrés
        } elseif ($nombre >= 225 && $nombre < 315) {
            $pitch = 270; // Ajuster le pitch à 270 si le nombre est proche de 270 degrés
        } else {
            $pitch = 0;   // Ajuster le pitch à 0 pour tous les autres cas
        }

        return $pitch;
    }



    public function attack(EntityDamageEvent $source): void
    {
        $key = CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE);

        if (!$source instanceof EntityDamageByEntityEvent) {
            return;
        }
        $damager = $source->getDamager();
        if ($damager instanceof Player) {
            if (!$damager->getInventory()->contains($key)) {
                $damager->sendMessage(Messages::message("§cVous n'avez pas de clé."));
                return;
            }

            $damager->getServer()->dispatchCommand($damager, "open_box rare");
            return;

            if ($this->isAnimation) {
                $damager->sendMessage(Messages::message("§cUn joueur utilise déjà cette box."));
                return;
            }

            $damager->getInventory()->removeItem($key->setCount(1));
            $this->isAnimation = true;
            $pk = AnimateEntityPacket::create("animation.box", "", "", 0, "box.controllers", 0, [$this->getId()]);

            foreach ($this->getViewers() as $player) {
                $player->getNetworkSession()->sendDataPacket($pk);
            }

            $player = $damager;
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                $this->getWorld()->addSound($this->getEyePos()->add(0, 1, 0), new ExplodeSound());
                $this->getWorld()->addParticle($this->getEyePos()->add(0, 1, 0), new HugeExplodeParticle());
            }), 20 * 4);


            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                if ($player->isConnected()) {
                    $content = $this->inv->getContents();
                    $chance = mt_rand(0, 999);
                    $recup = $content[0];
                    foreach ($content as $item) {
                        if ($item->hasNamedTag()) {
                            if ($item->getNamedTag()->getInt('proba', 5000) !== 5000) {
                                $proba = $item->getNamedTag()->getInt('proba', 1000);
                                if ($proba >= $chance) $recup = $item;
                            }
                        }
                    }
                    if ($player instanceof Player) {
                        if ($player->isConnected()) {
                            if ($recup !== null) {
                                $item = $recup;
                                if ($recup instanceof Item) {
                                    $item = $recup;
                                    $item->setLore($recup->getLore());
                                    $item->getNamedTag()->removeTag('proba');
                                    foreach ($recup->getEnchantments() as $enchantment) {
                                        $item->addEnchantment($enchantment);
                                    }
                                }
                                if ($player->getInventory()->canAddItem($item)) {
                                    $player->getInventory()->addItem($item);
                                } else $player->getWorld()->dropItem($player->getEyePos(), $item);
                                $this->setNameTag("§cBOOOOOOOOOOOOOOOOM");
                            } else $player->sendMessage(Messages::message("§cVous n'avez rien gagné !"));
                        }
                    }
                }
            }), 20 * 4);
            (new LogEvent($player->getName()." a ouvert une ".TextFormat::clean($this->getNameTag()), LogEvent::BOX_TYPE))->call();
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                $this->setNameTag("§6- §fBox Rare §6-\n\n§7§oTape moi");
                $this->isAnimation = false;
            }), 20 * 7);
        }
    }

    use SoundTrait;

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $this->inv->send($player);
        return parent::onInteract($player, $clickPos);
    }


    public function isFireProof(): bool
    {
        return true;
    }

    public function isOnFire(): bool
    {
        return false;
    }

    public function onUpdate(int $currentTick): bool
    {
        $this->extinguish();
        if ($this->isAnimation) {
            if ($this->time === 8) {
                $entitys = $this->getWorld()->getChunkEntities($this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
                foreach ($entitys as $player) {
                    if ($player instanceof Player) {
                        $pos = $player->getPosition();
                        $pk = new PlaySoundPacket();
                        $pk->soundName = 'firework.launch';
                        $pk->x = $pos->x;
                        $pk->y = $pos->y;
                        $pk->z = $pos->z;
                        $pk->pitch = 1;
                        $pk->volume = 5;
                        $player->getNetworkSession()->sendDataPacket($pk);
                    }
                }
                $this->time = 0;
            }
            $this->time++;
        }
        return true;
    }

    public function damageArmor(float $damage): void
    {

    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1, 1.2, 1.62);
    }

    protected function tryChangeMovement(): void
    {

    }

    public static function getNetworkTypeId(): string
    {
        return 'goldrush:box_rare';
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }
}