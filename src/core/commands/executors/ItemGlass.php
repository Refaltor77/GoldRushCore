<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\entities\ItemEntitySafe;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class ItemGlass extends Executor
{
    use UtilsTrait;

    public static array $isGlass = [];

    public function __construct(string $name = 'glass', string $description = "Mettre un item dans une vitre", ?string $usageMessage = null, array $aliases = [])
    {
        $this->setPermissionMessage(Messages::message("§cVous n'avez pas la permissions !"));
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('glass.use');
    }

    public function onRun(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(Messages::message("§cCommande exécutable uniquement sur le serveur."));
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/glass <place:remove>"));
            return;
        }

        if ($args[0] === 'place') {
            if (isset(self::$isGlass[$sender->getXuid()])) {
                unset(self::$isGlass[$sender->getXuid()]);
                $sender->sendMessage(Messages::message("§cAction annulée !"));
            } else {
                self::$isGlass[$sender->getXuid()] = $sender->getInventory()->getItemInHand();
                $sender->sendMessage(Messages::message("§aCliqué sur une vitre"));
            }
        } elseif ($args[0] === 'remove') {
            if (isset(self::$isGlass[$sender->getXuid()])) unset(self::$isGlass[$sender->getXuid()]);
            $nearest = $sender->getWorld()->getNearestEntity($sender->getEyePos(), 10, ItemEntitySafe::class);
            if ($nearest instanceof ItemEntitySafe) $nearest->close();
        } else   $sender->sendMessage(Messages::message("§c/glass <place:remove>"));
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'place');
        $this->addSubCommand(1, 'remove');
        return parent::loadOptions($player);
    }
}