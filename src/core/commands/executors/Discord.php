<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Discord extends Executor
{
    public function __construct(string $name = 'discord', string $description = "Voir votre compte discord", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $manager = $this->getPlugin()->getDiscordManager();

        $manager->getDiscordPseudo($sender, function (Player $player, string $pseudo) use ($manager) : void {
            if ($pseudo === 'not-link') {
                $player->sendErrorSound();
                $player->sendMessage(Messages::message("§cVotre discord n'est pas link, faite /link"));
                return;
            }

            $player->sendMessage(Messages::message("§fVotre compte discord link : §6" . $pseudo));
        });
    }
}