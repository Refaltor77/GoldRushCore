<?php

namespace core\commands\executors\secret;

use core\commands\Executor;
use core\items\fossils\FossilDiplodocus;
use core\items\fossils\FossilNodosaurus;
use core\items\fossils\FossilPterodactyle;
use core\items\fossils\Fossils;
use core\items\fossils\FossilsBrachiosaurus;
use core\items\fossils\FossilSpinosaure;
use core\items\fossils\FossilStegosaurus;
use core\items\fossils\FossilTriceratops;
use core\items\fossils\FossilTyrannosaureRex;
use core\items\fossils\FossilVelociraptor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class FossilAnalyser extends Executor
{
    use SoundTrait;

    public function __construct(string $name = "2478965235-fossil-command", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $classFossils = [
            FossilVelociraptor::class => 600,
            FossilTyrannosaureRex::class => 500,
            FossilTriceratops::class => 450,
            FossilStegosaurus::class => 300,
            FossilSpinosaure::class => 300,
            FossilsBrachiosaurus::class => 300,
            FossilPterodactyle::class => 250,
            FossilNodosaurus::class => 250,
            FossilDiplodocus::class => 250,
            Fossils::class => 100
        ];

        $found = false;
        foreach ($sender->getInventory()->getContents() as $slot => $item) {
            if (in_array($item::class, array_keys($classFossils))) {
                $found = true;
                break;
            }
        }

        if ($found) {
            $moneyAdd = 0;
            foreach ($sender->getInventory()->getContents() as $slot => $item) {
                if (in_array($item::class, array_keys($classFossils))) {
                    $moneyAdd += $classFossils[$item::class] * $item->getCount();
                    $sender->getInventory()->setItem($slot, VanillaItems::AIR());
                }
            }
            Main::getInstance()->getEconomyManager()->addMoney($sender, $moneyAdd);
            $this->sendSuccessSound($sender);
            $sender->sendMessage("§6[§7archéologue§6] §fJe vous ai acheté tous vos fossiles pour la somme de §f" . $moneyAdd . "§6$");
        } else {
            $this->sendErrorSound($sender);
            $sender->sendMessage("§6[§7archéologue§6] §cVous n'avez aucun fossile dans votre inventaire §4" . $sender->getName());
        }
    }
}