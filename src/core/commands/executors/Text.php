<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\entities\Bank;
use core\entities\BarText;
use core\entities\GoldrushText;
use core\entities\KothText;
use core\entities\Text as TextEntity;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class Text extends Executor
{
    use UtilsTrait;

    public static array $fastCache = [];

    public function __construct(string $name = 'text', string $description = "Crée un text flotant", ?string $usageMessage = null, array $aliases = [])
    {
        $this->setPermissionMessage(Messages::message("§cVous n'avez pas la permissions !"));
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('text.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            return;
        }
        if ($args[0] === 'remove') {
            $entity = $sender->getWorld()->getNearestEntity($sender->getEyePos(), 20);
            if ($entity instanceof TextEntity
                || $entity instanceof GoldrushText
                || $entity instanceof KothText
                || $entity instanceof BarText
                || $entity instanceof Bank) {
                $entity->flagForDespawn();
                $sender->sendMessage(Messages::message("§aTexte flottant supprimé !"));
            } else $sender->sendMessage(Messages::message("§cAucun texte flottant trouvé."));
        } elseif ($args[0] === 'goldrush_text_123456') {
            $entity = new GoldrushText($sender->getLocation());
            $entity->spawnToAll();
        }elseif ($args[0] === 'koth_text_123456') {
            $entity = new KothText($sender->getLocation());
            $entity->spawnToAll();
        }elseif ($args[0] === 'bank_text_123456') {
            $entity = new Bank($sender->getLocation());
            $entity->spawnToAll();
        }elseif ($args[0] === 'bar_text_123456') {
            $entity = new BarText($sender->getLocation());
            $entity->spawnToAll();
        } else {
            $name = str_replace('\n', "\n", implode(' ', $args));
            $entity = new TextEntity(
                $sender->getLocation(), null, $name);
            $entity->spawnToAll();
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'remove');
        return parent::loadOptions($player);
    }
}