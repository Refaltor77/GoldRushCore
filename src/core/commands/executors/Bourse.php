<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\entities\BourseText;
use core\Main;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\unicodes\FarmUnicode;
use core\unicodes\TextUnicode;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class Bourse extends Executor
{
    public function __construct(string $name = 'bourse', string $description = "Afficher la bourse du serveur", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {

        if (!isset($args[0])) {
            $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
            $sell = $config->get('shop');

            $ids = [
                'minecraft:melon_slice' => FarmUnicode::MELON . ' §f................................  §f{price}§6$',
                'minecraft:potatoes' => FarmUnicode::POTATO . ' §f................................  §f{price}§6$',
                'minecraft:carrots' => FarmUnicode::CARROT . ' §f................................  §f{price}§6$',
                'minecraft:wheat' => FarmUnicode::WHEAT . ' §f................................  §f{price}§6$',
                'minecraft:pumpkin' => FarmUnicode::CITROUILLE . ' §f................................  §f{price}§6$',
                'minecraft:sugarcane' => FarmUnicode::SUGARCANE . ' §f................................  §f{price}§6$',
                'minecraft:cactus' => FarmUnicode::CACTUS . ' §f................................  §f{price}§6$',
            ];


            $space = "\n";
            $msg = TextUnicode::LIGNE . " §lBOURSE §r§f" . TextUnicode::LIGNE . $space;

            foreach ($sell['Farming']['items'] as $index => $values) {
                $id = $values['idMeta'];
                if (in_array($id, array_keys($ids))) {
                    if (isset($values['sell'])) {
                        $msg .= str_replace('{price}', $values['sell'], $ids[$id]) . "\n\n";
                    }
                }
            }

            $msg .= "§f-------------------";
            $sender->sendMessage($msg);
        } else {
            if ($sender->hasPermission('bourse_spawn.use') || Server::getInstance()->isOp($sender->getName())) {
                if ($args[0] === 'spawn') {
                    $entity = new BourseText($sender->getLocation());
                    $entity->spawnToAll();
                } elseif ($args[0] === 'remove') {
                    $entity = $sender->getWorld()->getNearestEntity($sender->getEyePos(), 10, BourseText::class);
                    if ($entity instanceof BourseText) {
                        $entity->flagForDespawn();
                    }
                }
            }
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'spawn');
        $this->addSubCommand(1, 'remove');

        $this->addComment(0, 1, 'Créer un texte de bourse');
        $this->addComment(1, 1, 'Retirer un texte de bourse');
        return parent::loadOptions($player);
    }
}