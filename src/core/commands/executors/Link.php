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

class Link extends Executor
{
    public function __construct(string $name = 'link', string $description = "Link votre discord a goldrush", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§c/link <§4code§c> | Si vous avez pas de code, faite /link sur le discord de GoldRush."));
            return;
        }


        $code = $args[0];
        $manager = $this->getPlugin()->getDiscordManager();
        $manager->getDiscordPseudo($sender, function (Player $player, string $pseudo) use ($manager, $code) : void {
            if ($pseudo !== 'not-link') {
                $player->sendErrorSound();
                $player->sendMessage(Messages::message("§cVotre discord est déjà link, pour changer de compte discord, ouvrez un ticket."));
                return;
            }


            $manager->processLinkCallback($player, $code, function (Player $player, bool $found) use ($manager) : void {
                if (!$found) {
                    $player->sendMessage(Messages::message("§cLe code n'existe pas, si vous avez pas de code, faite /link sur le discord de GoldRush."));
                    return;
                }


                Main::getInstance()->jobsStorage->addItemInStorage($player, CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE));


                $player->sendSuccessSound();
                $player->sendMessage(Messages::message("§fVotre discord est désormais §6link !"));
                $player->sendMessage(Messages::message("§fUne récompense pour votre /link vient d'être ajouter, faite §6/rewards"));


                $manager->getParaineur($player, function (Player $player, string $idParaineur) use ($manager) : void {
                    if ($idParaineur == 'not-link') {
                        return;
                    }

                    $manager->getDiscordPseudoByIdDiscord($idParaineur, function (array $pseudoDiscordParaineur) use ($player, $manager, $idParaineur) : void {
                        if ($player->isConnected()) {
                            $player->sendMessage(Messages::message("§fLe joueur §6" . $pseudoDiscordParaineur[0] . " §fvous a parrainé, votre récompense a été ajoutée dans votre inventaire de récompenses. /rewards"));
                            $player->sendSuccessSound();

                            Main::getInstance()->jobsStorage->addItemInStorage($player, CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE));
                            Main::getInstance()->jobsStorage->saveUserCache($player);
                            Main::getInstance()->getEconomyManager()->addMoney($player, 10000);

                            $xuidParaineur = $pseudoDiscordParaineur[1];

                            $playerTarget = Main::getInstance()->getDataManager()->getPlayerXuid($xuidParaineur);
                            if ($playerTarget instanceof CustomPlayer) {
                                $manager->sendMessageDiscord($idParaineur, "Un joueur vient de rejoindre le serveur grâce à ton lien ! Tu viens de gagner 30 000 $.", "GoldRush - Parrainage");
                                $playerTarget->sendMessage("§6[§fDISCORD§6] : §fLe joueur §6" . $player->getName() . "§f vient de rejoindre grâce à ton lien d'invitation !");
                                Main::getInstance()->getEconomyManager()->addMoney($playerTarget, 30000);
                            } else Main::getInstance()->getEconomyManager()->addMoneyOffline($xuidParaineur, 30000);
                        }
                    });
                });
            });
        });
    }
}