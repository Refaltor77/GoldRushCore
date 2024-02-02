<?php

namespace core\commands\executors;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\entities\classements\TopDeath;
use core\entities\classements\TopEntity;
use core\entities\classements\TopFaction;
use core\entities\classements\TopGold;
use core\entities\classements\TopJobs;
use core\entities\classements\TopKill;
use core\entities\classements\TopMoney;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class TopCommands extends Executor
{
    public function __construct(string $name = 'topmanage', string $description = "Crée un classement", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('topmanage.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/topmanage <create:remove>"));
            return;
        }

        if (strtolower($args[0]) === 'create') {
            $sender->sendForm(new MenuForm(
                '- §6GoldRush Classements §r-',
                '§7Vous avez la possibilité de crée un classement en sélectionnant un bouton.',
                [
                    new Button('§6TopMoney', new Image('textures/items/copper_axe', Image::TYPE_PATH)),
                    new Button('§6TopKill', new Image('textures/items/gold_sword', Image::TYPE_PATH)),
                    new Button('§6TopFaction', new Image('textures/items/platinum_sword', Image::TYPE_PATH)),
                    new Button('§6TopDeath', new Image('textures/items/copper_sword', Image::TYPE_PATH)),
                   new Button('§6TopGold', new Image('textures/items/amethyst_hammer', Image::TYPE_PATH)),
                   new Button('§6TopJobs', new Image('textures/items/platinum_pickaxe', Image::TYPE_PATH)),


                ], function (Player $sender, Button $button): void {
                switch ($button->getValue()) {
                    case 0:
                        $top = new TopMoney($sender->getLocation(), null, "money");
                        $top->spawnToAll();
                        break;
                    case 1:
                        $top = new TopKill($sender->getLocation(), null, "kill");
                        $top->spawnToAll();
                        break;
                    case 2:
                        $top = new TopFaction($sender->getLocation(), null, "faction");
                        $top->spawnToAll();
                        break;
                    case 3:
                        $top = new TopDeath($sender->getLocation(), null, "death");
                        $top->spawnToAll();
                        break;
                    case 4:
                        $top = new TopGold($sender->getLocation(), null, "gold");
                        $top->spawnToAll();
                        break;
                    case 5:
                        $top = new TopJobs($sender->getLocation(), null, "jobs");
                        $top->spawnToAll();
                        break;
                }
            }
            ));
        } elseif (strtolower($args[0]) === 'remove') {
            $top = $sender->getWorld()->getNearestEntity($sender->getEyePos(), 10, TopEntity::class);
            if (!is_null($top)) {
                $top->flagForDespawn();
                $sender->sendMessage(Messages::message("§aClassement supprimé !"));
            } else {
                $sender->sendMessage(Messages::message("§cAucun classement trouvé."));
            }
        }
    }


    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'create');
        $this->addSubCommand(1, 'remove');
        return parent::loadOptions($player);
    }
}