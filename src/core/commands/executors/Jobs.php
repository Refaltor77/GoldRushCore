<?php

namespace core\commands\executors;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\forms\MenuForms;
use core\Main;
use core\messages\Messages;
use core\utils\Utils;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class Jobs extends Executor
{
    public function __construct(string $name = 'jobs', string $description = "Voir vos métiers", ?string $usageMessage = null, array $aliases = [
        'job', 'jobs','metier', 'metiers'
    ])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        MenuForms::sendJobsForms($sender);
    }


    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}