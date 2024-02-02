<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class RemoveAllEasterEgg extends Executor
{
    public function __construct(string $name = 'remove-easteregg', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('easteregg.delete.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'temp/easteregg.json', Config::JSON);
        $i = 0;
        foreach ($config->get('easteregg-list') as $index => $values) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($values['world']);
            if (!is_null($world)) {
                $x = $values['x'];
                $y = $values['y'];
                $z = $values['z'];

                $world->loadChunk($x >> 4, $z >> 4);
                $world->setBlock(new Position($x, $y, $z, $world), VanillaBlocks::AIR());
                $i++;
            }
        }

        $sender->sendMessage(Messages::message("§fVous avez supprimé §6" . $i . "§f easteregg(s)"));

        $config->setAll([
            "easteregg-list" => [],
            "players" => []
        ]);
        $config->save();
    }
}