<?php

namespace core\commands\executors;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\forms\FactionForms;
use core\forms\MenuForms;
use core\forms\ShopForms;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Menu extends Executor
{
    public function __construct(string $name = 'menu', string $description = "Accéder au menu de GoldRush", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        MenuForms::sendMenu($sender);
    }
}