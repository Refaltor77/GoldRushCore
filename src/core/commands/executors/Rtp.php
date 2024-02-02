<?php

namespace core\commands\executors;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\cooldown\BasicCooldown;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\tasks\Teleport;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Lava;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\normal\Normal;
use pocketmine\world\Position;
use pocketmine\world\World;

class Rtp extends Executor
{
    public function __construct(string $name = 'rtp', string $description = "Téléportation au hasard", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {

        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!BasicCooldown::validCustom($sender, 60 * 20)){
            $sender->sendForm(new MenuForm("f- §6Téléportation au hasard §f-",
                "Vous avez un cooldown sur la téléportation au hasard, voulez vous payer 5.000$ pour vous téléporter sur GoldRush ?", [
                    new Button("§aOUI"),
                    new Button("§cNON")
                ], function (Player $player, Button $button): void {

                if ($button->getValue() === 0) {
                    Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $money): void {
                        if ($money < 5000) {
                            $player->sendErrorSound();
                            $player->sendMessage("§cVous n'avez pas assez d'argent.");
                            return;
                        }

                        Main::getInstance()->getEconomyManager()->removeMoney($player, 5000);
                        $pos = $this->generatePosition();
                        while ($pos->getWorld()->getBlockAt($pos->getFloorX(), $pos->getFloorY() - 1, $pos->getFloorZ()) instanceof Lava) {
                            $pos = $this->generatePosition();
                        }

                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($player, $pos, function (Player $player, bool $success): void {
                            if (!$success) {
                                BasicCooldown::removeCooldown($player);
                                $player->sendMessage(Messages::message("§fLe cooldown de votre /rtp est libre."));
                            }
                        }), 20);
                    });
                }
            }));
        } else {
            $pos = $this->generatePosition();
            while ($pos->getWorld()->getBlockAt($pos->getFloorX(), $pos->getFloorY() - 1, $pos->getFloorZ()) instanceof Lava) {
                $pos = $this->generatePosition();
            }


            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender, $pos), 20);
        }
    }


    public function generatePosition(): Position {

        $randomCoordinate = [
          "x" => [
              mt_rand(2000, 4000),
              mt_rand(-4000,-2000)
          ],
          "y"=> [
              mt_rand(2000, 4000),
              mt_rand(-4000,-2000)
          ]
        ];

        $x = $randomCoordinate["x"][mt_rand(0, 1)];
        $z = $randomCoordinate["y"][mt_rand(0, 1)];

        $y = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getHighestBlockAt($x, $z) + 1;
        $pos = new Position($x, $y, $z, Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld());
        return $pos;
    }
}