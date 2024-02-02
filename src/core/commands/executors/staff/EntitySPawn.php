<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\entities\boss\Ogre;
use core\entities\cosmetics\Rideau;
use core\entities\DoorBox;
use core\entities\Roulette;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class EntitySPawn extends Executor
{
    public function __construct(string $name = 'entity_spawn', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('entity.spawn.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) return;
        if (!isset($args[1])) return;

        switch ($args[0]) {
            case 'spawn':
                $entity = match ($args[1]) {
                    'roulette' => Roulette::class,
                    'rideau' => Rideau::class,
                    'door' => DoorBox::class,
                    'ogre' => Ogre::class
                };
                $entity = new $entity($sender->getLocation());
                $entity->spawnToAll();
                break;
            case 'remove':
                $entity = match ($args[1]) {
                    'roulette' => Roulette::class,
                    'rideau' => Rideau::class,
                    'door' => DoorBox::class,
                    'ogre' => Ogre::class
                };

                $entitySearch = $sender->getWorld()->getNearestEntity($sender->getPosition(), 10, $entity);

                if ($entitySearch::class === $entity) {
                    $entitySearch->flagForDespawn();
                }
                break;
        }
    }
}