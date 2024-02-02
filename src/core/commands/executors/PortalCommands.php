<?php

namespace core\commands\executors;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\api\form\ModalForm;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class PortalCommands extends Executor
{
    public static array $fastCache = [];
    public static array $fastCache2 = [];

    public function __construct(string $name = 'portal', string $description = "Crée un portail", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('portal.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/portal <create:list>"));
            return;
        }

        if (strtolower($args[0]) === 'create') {

            if (isset(self::$fastCache[$sender->getUniqueId()->toString()])) {
                unset(self::$fastCache[$sender->getUniqueId()->toString()]);
                $sender->sendMessage(Messages::message("§eVous avez annulé la création de votre portail."));
            } else {
                $sender->sendMessage(Messages::message("§eVeuillez casser deux blocs pour définir les coordonnées du portail."));
                self::$fastCache[$sender->getUniqueId()->toString()] = ['1' => null, '2' => null];
            }

        } elseif (strtolower($args[0]) === 'list') {

            $array = Main::getInstance()->getPortalManager()->cache;
            $buttons = [];
            $arrayValue = [];
            $i = 0;
            foreach ($array as $name => $values) {
                $buttons[] = new Button('§c- §6' . $name . ' §c-', new Image('textures/items/apple'));
                $arrayValue[$i] = $name;
                $i++;
            }

            $sender->sendForm(new MenuForm(
                '§6- §eListe des portails§6-',
                '§7Voici la liste des portails présentes sur le serveur, vous pouvez modifier leurs atributs !',
                $buttons,
                function (Player $player, Button $button) use ($arrayValue): void {
                    $value = $button->getValue();
                    $name = $arrayValue[$value];
                    $player->sendForm(new MenuForm(
                        "§6- §eCaratéristique du portail §c{$name} §6-",
                        '§7Vous pouvez modifier les attributs du portail si vous le souhaitez.',
                        [
                            new Button('§eRedéfinir les coordonnées', new Image('textures/items/compass_item')),
                            new Button('§cSupprimer le portail [§4/!\§c]', new Image('textures/items/flint_and_steel'))
                        ],
                        function (Player $player, Button $button) use ($name): void {
                            $value = $button->getValue();
                            switch ($value) {
                                case 0:
                                    if (!isset(self::$fastCache2[$player->getUniqueId()->toString()])) {
                                        self::$fastCache2[$player->getUniqueId()->toString()] = ['1' => null, '2' => null, 'name' => $name];
                                    } else {
                                        unset(self::$fastCache2[$player->getUniqueId()->toString()]);
                                        $player->sendMessage(Messages::message("Tu as annulé ton action !"));
                                    }
                                    break;
                                case 1:
                                    $player->sendForm(new ModalForm(
                                        '§4- §cAttention !§c -',
                                        "§cSi vous acceptez la suppression de votre portail, il n’y a aucun moyen de récupérer les données de celui-ci !",
                                        function (Player $player, bool $response) use ($name): void {
                                            if ($response) {
                                                if (Main::getInstance()->getPortalManager()->existPortal($name)) {
                                                    unset(Main::getInstance()->getPortalManager()->cache[$name]);
                                                }
                                                $player->sendMessage(Messages::message("§eVous avez supprimé le portail §6{$name} §a!"));
                                            } else $player->sendMessage(Messages::message("§aVous avez annulé la suppression du portail 6{$name} §a!"));
                                        }
                                    ));
                                    break;
                            }
                        }
                    ));
                }
            ));

        } else {
            $sender->sendMessage(Messages::message("§c/portal <create:list>"));
        }
    }

    public function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'create');
        $this->addSubCommand(1, 'list');
        return parent::loadOptions($player);
    }
}