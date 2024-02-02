<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\elements\Toggle;
use core\api\form\MenuForm;
use core\api\form\ModalForm;
use core\commands\Executor;
use core\Main;
use core\managers\area\AreaBuild;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class AreaCommands extends Executor
{
    public static array $fastCache = [];
    public static array $fastCache2 = [];


    public function __construct(string $name = 'area', string $description = "Commandes de zone", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('area.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(Messages::message("§cCommande exécutable uniquement sur le serveur."));
            return;
        }

        if (isset($args[0])) {
            switch (strtolower($args[0])) {
                case 'create':
                    $uuid = $sender->getUniqueId()->getBytes();
                    if (!isset(self::$fastCache[$uuid])) {
                        self::$fastCache[$uuid] = ['1' => null, '2' => null];
                        $sender->sendMessage(Messages::message("§eVeuillez casser deux blocs pour définir les coordonnées de la zone."));
                    } else {
                        unset(self::$fastCache[$uuid]);
                        $sender->sendMessage(Messages::message("§eVous avez annulé la création de votre zone protéger."));
                    }
                    break;
                case 'list':
                    $array = Main::getInstance()->getAreaManager()->cache;
                    $buttons = [];
                    $arrayValue = [];
                    $i = 0;
                    foreach ($array as $name => $values) {
                        $buttons[] = new Button('§c- §6' . $name . ' §c-', new Image('textures/items/apple'));
                        $arrayValue[$i] = $name;
                        $i++;
                    }

                    $sender->sendForm(new MenuForm(
                        '§6- §eListe des zones§6-',
                        '§7Voici la liste des zones présentes sur le serveur, vous pouvez modifier leurs atributs !',
                        $buttons,
                        function (Player $player, Button $button) use ($arrayValue): void {
                            $value = $button->getValue();
                            $name = $arrayValue[$value];
                            $flags = Main::getInstance()->getAreaManager()->getFlagsByName($name);
                            $player->sendForm(new MenuForm(
                                "§6- §eCaratéristique de la zone §c{$name} §6-",
                                '§7Vous pouvez modifier les attributs de cette zone si vous le souhaitez.',
                                [
                                    new Button('§aModifier les attributs', new Image('textures/items/carrot')),
                                    new Button('§eRedéfinir les coordonnées', new Image('textures/items/compass_item')),
                                    new Button('§cSupprimer la zone [§4/!\§c]', new Image('textures/items/flint_and_steel'))
                                ],
                                function (Player $player, Button $button) use ($name, $flags): void {
                                    $value = $button->getValue();
                                    switch ($value) {
                                        case 0:
                                            $player->sendForm(new CustomForm(
                                                "§6- §eModification de la zone §c{$name} §6-",
                                                [
                                                    new Toggle('§6» §ePVP', $flags['pvp']),
                                                    new Toggle('§6» §ePlacer des blocs', $flags['place']),
                                                    new Toggle('§6» §eCasser des blocs', $flags['break']),
                                                    new Toggle('§6» §eFaim', $flags['hunger']),
                                                    new Toggle('§6» §eJeter des items', $flags['dropItem']),
                                                    new Toggle('§6» §eLes tnt exploses', $flags['tnt']),
                                                    new Toggle('§6» §eCommandes [/]', $flags['cmd']),
                                                    new Toggle('§6» §eEnvoi de message dans le chat', $flags['chat']),
                                                    new Toggle('§6» §eConsommer un item', $flags['consume']),
                                                ],
                                                function (Player $player, CustomFormResponse $response) use ($name): void {
                                                    list($pvp, $place, $break, $hunger, $drop, $tnt, $cmd, $chat, $consume) = $response->getValues();
                                                    $flags = AreaBuild::createBaseFlags();
                                                    $flags['pvp'] = $pvp;
                                                    $flags['place'] = $place;
                                                    $flags['break'] = $break;
                                                    $flags['hunger'] = $hunger;
                                                    $flags['dropItem'] = $drop;
                                                    $flags['tnt'] = $tnt;
                                                    $flags['cmd'] = $cmd;
                                                    $flags['chat'] = $chat;
                                                    $flags['consume'] = $consume;
                                                    Main::getInstance()->getAreaManager()->setFlagsByName($name, $flags);
                                                    $player->sendMessage(Messages::message("§aLa zone §6$name §aa été modifié avec succès !"));
                                                }
                                            ));
                                            break;
                                        case 1:
                                            $uuid = $player->getUniqueId()->getBytes();
                                            if (!isset(self::$fastCache2[$uuid])) {
                                                self::$fastCache2[$uuid] = ['1' => null, '2' => null, 'name' => $name];
                                            } else {
                                                unset(self::$fastCache2[$uuid]);
                                                $player->sendMessage(Messages::message("Tu as annulé ton action !"));
                                            }
                                            break;
                                        case 2:
                                            $player->sendForm(new ModalForm(
                                                '§4- §cAttention !§c -',
                                                "§cSi vous acceptez la suppression de votre zone, il n’y a aucun moyen de récupérer les données de celui-ci !",
                                                function (Player $player, bool $response) use ($name): void {
                                                    if ($response) {
                                                        Main::getInstance()->getAreaManager()->deleteAreaByName($name);
                                                        $player->sendMessage(Messages::message("§eVous avez supprimé la zone §6{$name} §a!"));
                                                    } else $player->sendMessage(Messages::message("§aVous avez annulé la suppression de la zone 6{$name} §a!"));
                                                }
                                            ));
                                            break;
                                    }
                                }
                            ));
                        }
                    ));
                    break;
            }
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'create');
        $this->addSubCommand(1, 'list');

        $this->addComment(0, 1, 'Crée une zone protéger');
        $this->addComment(1, 1, 'Gérer les zones protégées');


        return parent::loadOptions($player);
    }
}