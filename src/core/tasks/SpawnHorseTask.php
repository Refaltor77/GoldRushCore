<?php

namespace core\tasks;

use core\entities\horse\Horse;
use core\entities\horse\HorseAmethyst;
use core\entities\horse\HorseCopper;
use core\entities\horse\HorseEmerald;
use core\entities\horse\HorseGold;
use core\entities\horse\HorsePlatinum;
use core\items\ExtraVanillaItem;
use core\messages\Messages;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;

class SpawnHorseTask extends Task
{

    private int $time = 5;
    private Player $player;
    private float $speed;
    public static array $isSpawn = [];
    public Position $base;
    public Item $item;

    use SoundTrait;

    public function __construct(Player $player, float $speed, Item $item)
    {
        $this->base = $player->getPosition();
        $this->player = $player;
        $this->speed = $speed;
        $this->item = $item;
        self::$isSpawn[$player->getXuid()] = true;
    }

    public function onRun(): void
    {
        $base = $this->base;
        $pos = $this->player->getPosition();


        if (!$this->player->isConnected()) {
            unset(self::$isSpawn[$this->player->getXuid()]);
            if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
            return;
        }


        if (!$this->player->getInventory()->getItemInHand()->equals($this->item)) {
            $this->player->sendMessage(Messages::message("§cVous devez garder votre monture dans la main."));
            unset(self::$isSpawn[$this->player->getXuid()]);
            if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
            return;
        }


        if ($pos->getFloorX() !== $base->getFloorX() || $pos->getFloorY() !== $base->getFloorY() || $pos->getFloorZ() !== $base->getFloorZ()) {
            $this->sendErrorSound($this->player);
            $this->player->sendMessage(Messages::message("§cVous avez bougé, annulation de l'apparition de la monture."));
            if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
            unset(self::$isSpawn[$this->player->getXuid()]);
            return;
        }



        if ($this->time <= 0) {
            $horse = new Horse($this->player->getLocation());
            switch ($this->item->getTypeId()) {
                case CustomiesItemFactory::getInstance()->get(Ids::HORSE_ARMOR_COPPER)->getTypeId():
                    $horse = new Horse($this->player->getLocation());
                    break;
                case CustomiesItemFactory::getInstance()->get(Ids::HORSE_ARMOR_EMERALD)->getTypeId():
                    $horse = new Horse($this->player->getLocation());
                    break;
                case CustomiesItemFactory::getInstance()->get(Ids::HORSE_ARMOR_AMETHYST)->getTypeId():
                    $horse = new Horse($this->player->getLocation());
                    break;
                case CustomiesItemFactory::getInstance()->get(Ids::HORSE_ARMOR_PLATINUM)->getTypeId():
                    $horse = new Horse($this->player->getLocation());
                    break;
                case CustomiesItemFactory::getInstance()->get(Ids::HORSE_ARMOR_GOLD)->getTypeId():
                    $horse = new Horse($this->player->getLocation());
                    break;
            }
            $horse->setVitesse($this->speed);
            $horse->spawnToAllRiding();
            $horse->setRider($this->player);
            unset(self::$isSpawn[$this->player->getXuid()]);
            $this->getHandler()->cancel();
        }
        $this->player->sendActionBarMessage("§eVotre monture arrive dans §c" . $this->calculTimeString() . " seconde(s)");
        $this->time--;
    }


    public function calculTimeString(): string {
        $time = $this->time;
        $timeBase = 5;
        $diff = $timeBase - $time;


        return str_repeat("§a■", $diff) . str_repeat("§c■", $time);
    }
}