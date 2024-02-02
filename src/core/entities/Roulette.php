<?php

namespace core\entities;

use Cassandra\Custom;
use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Input;
use core\api\form\MenuForm;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\utils\Utils;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\player\Player;

class Roulette extends Entity
{
    public bool $isOccuped = false;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $location->pitch = 0.0;
        $location->yaw = round($location->getYaw() / 90) * 90;
        $location->x = $location->getFloorX() + 0.5;
        $location->y = $location->getFloorY();
        $location->z = $location->getFloorZ() + 0.5;
        parent::__construct($location, $nbt);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.5, 1.0);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }

    public function onUpdate(int $currentTick): bool
    {
        return false;
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
        if ($source instanceof EntityDamageByEntityEvent) {
            $player = $source->getDamager();
            if ($player instanceof CustomPlayer) {
                if ($this->isOccuped) {
                    $player->sendMessage(Messages::message("§cLa roulette est déjà en cours ! Patience :)"));
                    return;
                }

                $player->sendForm(new MenuForm("ROULETTE", "§cBienvenue au casinoooo !§f Je suis Bucky ! Le manager de la roulette, mise de l'argent sur une couleur et empoche la fortune !", [
                    new Button("green"),
                    new Button("red"),
                    new Button("grey"),
                ], function (Player $player, Button $button): void {
                    if ($this->isOccuped) {
                        $player->sendMessage(Messages::message("§cLa roulette est déjà en cours ! Patience :)"));
                        return;
                    }

                    $color = match ($button->getText()) {
                        "green" => "green",
                        "red" => "red",
                        "grey" => "grey"
                    };


                    $player->sendForm(new CustomForm("Misez votre argent", [
                        new Input("Montant", "1000")
                    ], function (Player $player, CustomFormResponse $response) use ($color) : void {
                        if ($this->isOccuped) {
                            $player->sendMessage(Messages::message("§cLa roulette est déjà en cours ! Patience :)"));
                            return;
                        }

                        $value = $response->getInput()->getValue();
                        if (!(int)$value) {
                            $player->sendMessage(Messages::message("§cVous devez miser un chiffre entier"));
                            $player->sendErrorSound();
                            return;
                        }

                        Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $money) use ($color, $value) : void  {
                            if ($this->isOccuped) {
                                $player->sendMessage(Messages::message("§cLa roulette est déjà en cours ! Patience :)"));
                                return;
                            }

                            if ($money < $value) {
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent."));
                                $player->sendErrorSound();
                                return;
                            }

                            Main::getInstance()->getEconomyManager()->removeMoney($player, $value);
                            $player->sendMessage(Messages::message("§fLancement de la roulette !"));

                            $couleurs = [
                                'grey' => 1,   // chance sur 9
                                'green'  => 4,   // chance sur 2
                                'red' => 4    // chance sur 2
                            ];


                            $indexCouleur = $this->choisirCouleurPonderee($couleurs);
                            $colorChoice = $indexCouleur;

                            $animName = match ($indexCouleur) {
                                "grey" => "animation.roulette.grey",
                                "green" => "animation.roulette.green",
                                "red" => "animation.roulette.red",
                                default => null,
                            };

                            $pk = AnimateEntityPacket::create($animName, "", "", 0, "", 0, [$this->getId()]);
                            $this->getWorld()->broadcastPacketToViewers($this->getPosition(), $pk);
                            $this->isOccuped = true;
                            Utils::timeout(function () use ($player, $colorChoice, $value, $color) : void  {
                                if (!$this->isFlaggedForDespawn()) {
                                    $this->isOccuped = false;
                                    if ($player->isConnected()) {
                                        if ($color == $colorChoice) {
                                            $multiple = match ($colorChoice) {
                                                "grey" => 2,
                                                "green" => 4,
                                                "red" => 2,
                                                default => 2
                                            };

                                            $player->sendMessage(Messages::message("§fBravo ! Tu vient de multiplier ton gain par §6" . $multiple));
                                            $player->sendSuccessSound();
                                            Main::getInstance()->getEconomyManager()->addMoney($player, intval($value * $multiple));
                                        } else {
                                            $player->sendErrorSound();
                                            $player->sendMessage(Messages::message("§cTu as perdu ! Tente t'as chance une autre fois :)"));
                                        }
                                    }
                                }
                            }, 20 * 4 + 10);

                            if (is_null($animName)) return;
                        });
                    }));
                }));
            }
        }
    }


    public function choisirCouleurPonderee($couleurs) {


        $chance = mt_rand(0, 100);
        if ($chance <= 5) {
            return 'green';
        } else {
            if (mt_rand(0, 1) === 1) {
                return 'red';
            } else return 'grey';
        }
    }

    protected function tryChangeMovement(): void
    {

    }

    public static function getNetworkTypeId(): string
    {
        return 'goldrush:roulette';
    }
}