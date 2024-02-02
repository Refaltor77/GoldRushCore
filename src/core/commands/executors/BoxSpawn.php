<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\entities\BoxBlackGold;
use core\entities\BoxBoost;
use core\entities\BoxCommon;
use core\entities\BoxFortune;
use core\entities\BoxLegendary;
use core\entities\BoxMythical;
use core\entities\BoxRare;
use core\messages\Messages;
use core\player\CustomPlayer;
use customiesdevs\customies\entity\CustomiesEntityFactory;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class BoxSpawn extends Executor
{
    public function __construct(string $name = 'box_spawn', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('box_spawn.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/box_spawn <common, rare, legendary, mythical, boost, black_gold> <create, remove>"));
            return;
        }

        if (!isset($args[1])) {
            $sender->sendMessage(Messages::message("§c/box_spawn <common, rare, legendary, mythical, boost, black_gold, fortune> <create, remove>"));
            return;
        }

        if ($args[1] === 'create') {
            switch ($args[0]) {
                case 'common':
                    $entity = new BoxCommon($sender->getLocation());
                    $entity->spawnToAll();
                    break;
                case 'rare':
                    $entity = new BoxRare($sender->getLocation());
                    $entity->spawnToAll();
                    break;
                case 'boost':
                    $entity = new BoxBoost($sender->getLocation());
                    $entity->spawnToAll();
                    break;
                case 'fortune':
                    $entity = new BoxFortune($sender->getLocation());
                    $entity->spawnToAll();
                    break;
                case 'legendary':
                    $entity = new BoxLegendary($sender->getLocation());
                    $entity->spawnToAll();
                    break;
                case 'mythical':
                    $entity = new BoxMythical($sender->getLocation());
                    $entity->spawnToAll();
                    break;
                case 'black_gold':
                    $entity = new BoxBlackGold($sender->getLocation());
                    $entity->spawnToAll();
                    break;
            }
        } elseif ($args[1] === 'remove') {
            foreach ([
                BoxRare::class,
                         BoxCommon::class,
                         BoxLegendary::class,
                         BoxBlackGold::class,
                         BoxBoost::class,
                         BoxMythical::class,
                         BoxFortune::class,
                         BoxRare::class,
                         ] as $class) {
                $entity = $sender->getWorld()->getNearestEntity($sender->getEyePos(), 10, $class);
                if ($entity !== null) $entity->flagForDespawn();
            }
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, "Boxs", true, "Nom de la box", [
            "common", "rare", "legendary", "mythical", "boost", "black_gold"
        ]);
        $this->addOptionEnum(1, "action", true, "action", [
            "create", "remove"
        ]);
        return parent::loadOptions($player);
    }
}