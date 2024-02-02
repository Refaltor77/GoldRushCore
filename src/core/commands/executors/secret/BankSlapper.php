<?php

namespace core\commands\executors\secret;

use core\commands\Executor;
use core\items\fossils\FossilDiplodocus;
use core\items\fossils\FossilNodosaurus;
use core\items\fossils\FossilPterodactyle;
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

class BankSlapper extends Executor
{
    use SoundTrait;

    public function __construct(string $name = "2694856-bank_slapper", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        
    }
}