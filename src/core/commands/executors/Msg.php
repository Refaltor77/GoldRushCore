<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\command\Command;
use pocketmine\command\defaults\TellCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Msg extends Executor
{
    public function __construct(string $name = "msg", string $description = "Envoie un message privé au joueur spécifié", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if(count($args) < 2){
            throw new InvalidCommandSyntaxException();
        }

        $player = $sender->getServer()->getPlayerByPrefix(array_shift($args));

        if($player === $sender){
            $sender->sendMessage(KnownTranslationFactory::commands_message_sameTarget()->prefix(TextFormat::RED));
            return true;
        }

        if($player instanceof Player){
            if(!Main::getInstance()->getSettingsManager()->getSetting($player,"private-chat")){
                if ($sender->isOp()) {

                } else {
                    $sender->sendMessage("§cLe joueur a désactivé les messages privés.");
                    return true;
                }
            }
            $message = implode(" ", $args);
            $sender->sendMessage("§7[§6Moi §7-> §6{$player->getName()}§7] §f{$message}");
            $player->sendMessage("§7[§6{$sender->getName()} §7-> §6Moi§7] §f{$message}");
            Main::$TELL[$player->getName()] = $sender->getName();
        }else{
            $sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
        }

        return true;
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}