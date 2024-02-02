<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\managers\cosmetic\CosmeticManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Cosmetiques;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class AddCosmetic extends Executor
{
    public function __construct(string $name = 'addcosmetic', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('cosmetics.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§c/addcosmetics <add:remove> <hats:back:capes:others:costumes:pets> <name> <playerName>"));
            return;
        }

        if (!isset($args[1])) {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§c/addcosmetics <add:remove> <hats:back:capes:others:costumes:pets> <name> <playerName>"));
            return;
        }

        if (!isset($args[2])) {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§c/addcosmetics <add:remove> <hats:back:capes:others:costumes:pets> <name> <playerName>"));
            return;
        }

        if (!isset($args[3])) {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§c/addcosmetics <add:remove> <hats:back:capes:others:costumes:pets> <name> <playerName>"));
            return;
        }


        $playerTarget = Server::getInstance()->getPlayerByPrefix($args[3]);
        if (!$playerTarget instanceof CustomPlayer) {
            $sender->sendMessage(Messages::message("§cLe joueur n'existe pas."));
            return;
        }


        switch ($args[0]) {
            default:
                $sender->sendErrorSound();
                $sender->sendMessage(Messages::message("§c/addcosmetics <add:remove> <hats:back:capes:others:costumes:pets> <name> <playerName>"));
                break;
            case 'add':
                switch ($args[1]) {
                    case 'hats':
                        $data = Cosmetiques::HEADS;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }


                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->addCosmetic($playerTarget->getXuid(), $args[2], "head");
                        break;
                    case 'back':
                        $data = Cosmetiques::BACK;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->addCosmetic($playerTarget->getXuid(), $args[2], "back");
                        break;
                    case 'capes':
                        $data = Cosmetiques::CAPES;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $index);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->addCosmetic($playerTarget->getXuid(), $args[2], "cape");
                        break;
                    case 'others':
                        $data = Cosmetiques::OTHERS;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->addCosmetic($playerTarget->getXuid(), $args[2], "other");
                        break;
                    case 'costumes':
                        $data = Cosmetiques::COSTUMES;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->addCosmetic($playerTarget->getXuid(), $args[2], "costumes");
                        break;
                }
                break;
            case 'remove':
                switch ($args[1]) {
                    case 'hats':
                        $data = Cosmetiques::HEADS;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->removeCosmetic($playerTarget->getXuid(), $args[2], "head");
                        break;
                    case 'back':
                        $data = Cosmetiques::BACK;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->removeCosmetic($playerTarget->getXuid(), $args[2], "back");
                        break;
                    case 'capes':
                        $data = Cosmetiques::CAPES;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->removeCosmetic($playerTarget->getXuid(), $args[2], "cape");
                        break;
                    case 'others':
                        $data = Cosmetiques::OTHERS;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->removeCosmetic($playerTarget->getXuid(), $args[2], "other");
                        break;
                    case 'costumes':
                        $data = Cosmetiques::COSTUMES;
                        $dataQueried = [];
                        foreach ($data as $string => $index) {
                            $dataQueried[] = str_replace('goldrush:', '', $string);
                        }

                        if (!in_array($args[2], $dataQueried)) {
                            $sender->sendMessage(Messages::message("§cLe cosmétique n'existe pas."));
                            return;
                        }

                        Main::getInstance()->getCosmeticManager()->removeCosmetic($playerTarget->getXuid(), $args[2], "costumes");
                        break;
                }
                break;
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, "add");
        $this->addSubCommand(1, "remove");


        $this->addOptionEnum(1, "type", true, "cosmetics", [
            "hats", "back", "capes", "others", "costumes", "pets"
        ]);


        $arrayQueried = [];
        foreach (Cosmetiques::ALL as $string => $number) {
            $arrayQueried[] = str_replace('goldrush:', "", $string);
        }

        $this->addOptionEnum(2, "cosmetic_name", true, "cosmetic_name", $arrayQueried);

        return parent::loadOptions($player);
    }
}