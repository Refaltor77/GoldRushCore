<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\forms\ShopForms;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Shop extends Executor
{
    public function __construct(string $name = 'shop', string $description = "Voir le shop", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }


    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            ShopForms::sendMainMenuShop($sender, ShopForms::CATEGORY_ORES);
        } else {
            switch (strtolower($args[0])) {
                case "blocks":
                    ShopForms::sendMainMenuShop($sender, ShopForms::CATEGORY_BLOCKS);
                    break;
                case "farming":
                    ShopForms::sendMainMenuShop($sender, ShopForms::CATEGORY_FARM);
                    break;
                case "decoration":
                    ShopForms::sendMainMenuShop($sender, ShopForms::CATEGORY_DECO);
                    break;
                case "minerais":
                    ShopForms::sendMainMenuShop($sender, ShopForms::CATEGORY_ORES);
                    break;
                case "utilitaires":
                    ShopForms::sendMainMenuShop($sender, ShopForms::CATEGORY_UTILS);
                    break;
                case "mobs":
                    ShopForms::sendMainMenuShop($sender, ShopForms::CATEGORY_MOBS);
                    break;
                case "autres":
                    ShopForms::sendMainMenuShop($sender, ShopForms::CATEGORY_OTHERS);
                    break;
            }
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, "blocks");
        $this->addComment(0, 1, "Sections pour acheter des blocks");
        $this->addSubCommand(1, "farming");
        $this->addComment(1, 1, "Sections pour acheter des graines ou vendre des cultures");
        $this->addSubCommand(2, "decoration");
        $this->addComment(2, 1, "Sections pour acheter de la décoration");
        $this->addSubCommand(3, "minerais");
        $this->addComment(3, 1, "Sections pour acheter ou vendre des minerais");
        $this->addSubCommand(4, "utilitaires");
        $this->addComment(4, 1, "Sections pour acheter des items utiles");
        $this->addSubCommand(5, "mobs");
        $this->addComment(5, 1, "Sections pour acheter ou vendre des loots de monstre");
        $this->addSubCommand(6, "autres");
        $this->addComment(6, 1, "Sections qui contient le reste du shop");

        return parent::loadOptions($player);
    }
}