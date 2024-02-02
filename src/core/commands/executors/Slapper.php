<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\api\form\elements\Toggle;
use core\commands\Executor;
use core\entities\Slapper as SlapperEntity;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class Slapper extends Executor
{
    use UtilsTrait;

    public function __construct()
    {
        parent::__construct("slapper", "Créer un slapper", "", []);
        $this->setPermission("slapper.execute");
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/slapper <create:remove>"));
            return;
        }

        if (strtolower($args[0]) === 'create') {
            $sender->sendForm(new CustomForm('- §6Créer un slapper §r-', [
                new Label("§7Bienvenue sur l'interface de la création des slappers, si vous ne voulez pas mettre de commande, laissez la case vide."),
                new Input("§6» §eNom du slapper", "Je suis steve batard"),
                new Input("§6» §eCommande", "feed"),
                new Toggle("§6» §eDanse", false)
            ], function (Player $player, CustomFormResponse $response): void {
                list($name, $cmd, $dance) = $response->getValues();
                $slapper = new SlapperEntity($player->getLocation(), $player->getSkin(), $name, $cmd,$dance);
                $slapper->getArmorInventory()->setContents($player->getArmorInventory()->getContents());
                $slapper->getInventory()->setItemInHand($player->getInventory()->getItemInHand());
                $slapper->spawnToAll();
            }));

        } elseif (strtolower($args[0]) === 'remove') {
            $entity = $sender->getWorld()->getNearestEntity($sender->getPosition(), 10, SlapperEntity::class);
            if ($entity instanceof SlapperEntity) {
                $entity->flagForDespawn();
                $sender->sendMessage(Messages::message("§aSlapper supprimé !"));
            } else $sender->sendMessage(Messages::message("§cAucun slapper détecté."));
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, "create");
        $this->addSubCommand(1, "remove");

        $this->addComment(0, 1, "Créer un slapper");
        $this->addComment(1, 1, "Retirer un slapper");
        return parent::loadOptions($player);
    }
}