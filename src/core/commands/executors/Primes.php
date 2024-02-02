<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class Primes extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'primes', string $description = "Voir les primes actuels", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $primes = Main::getInstance()->getPrimeManager()->getAllPrimeArray();

        $msg = "§6---- §fPRIMES §6----\n";
        foreach ($primes as $name => $price) {
            $msg .= "§6" . $name . " §f=> " . $price . "§6$\n";
        }
        $sender->sendMessage($msg);
        $this->sendPop($sender);
    }
}