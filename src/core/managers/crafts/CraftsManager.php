<?php

namespace core\managers\crafts;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\player\CustomPlayer;
use core\settings\BlockIds;
use core\settings\Ids;
use core\sql\SQL;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\ClosureTask;

class CraftsManager extends Manager
{
    public array $cache = [];
    public array $blocked_items = [];

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);

        // Initialisez la base de données si elle n'existe pas déjà
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS `player_crafts` (xuid VARCHAR(255) PRIMARY KEY, blocked_crafts TEXT, unlocked_crafts TEXT)");


        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
            $ids = new \ReflectionClass(Ids::class);
            $constants = $ids->getConstants();
            foreach ($constants as $constant => $id) {
                if (!str_contains($id, "wood") &&
                    !str_contains($id, "copper") &&
                    !str_contains($id, "stone") &&
                    !str_contains($id, "ingot") &&
                    !str_contains($id, "nugget") &&
                    !str_contains($id, "powder")) {
                    if ($id !== 'goldrush:' && !in_array($id, [
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::KEYPAD)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::EMPTY_BOTTLE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_HEAL)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_SPEED)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_HASTE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUR)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_FORCE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_DYNAMITE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::WATER_DYNAMITE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HOE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HOE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::COPPER_HOE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::GOLD_HOE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HOE)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::BARREL)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_CHEST)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::EMERALD_CHEST)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::AMETHYST_CHEST)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::GOLD_BLOCK)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::HOOD_HELMET)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_EMERALD)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::BONE_AXE_1)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::GOLD_NUGGET)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::COPPER_NUGGET)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_NUGGET)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::SULFUR_POWDER)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::COPPER_POWDER)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_POWDER)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::BUCKET_COPPER_EMPTY)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::BUCKET_GOLD_EMPTY)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::BUCKET_PLATINUM_EMPTY)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::FARMTOOLS)->getTextureString(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->getName(),
                            "goldrush:" . CustomiesItemFactory::getInstance()->get(Ids::PICKAXE_SPAWNER)->getTextureString()
                        ])) {

                        $item = CustomiesItemFactory::getInstance()->get($id);
                        if (method_exists(get_class($item), "getTextureString")) {
                            $this->blocked_items[] = CustomiesItemFactory::getInstance()->get($id)->getTextureString();
                        } else {
                            $this->blocked_items[] = CustomiesItemFactory::getInstance()->get($id)->getName();
                        }
                    }
                }
            }

        }), 40);
    }





    public function loadPlayer(CustomPlayer $player): void {
        $this->playerDataExistsAsync($player, function (bool $exist) use ($player): void {
            if ($player->isConnected()) {
                $xuid = $player->getXuid();
                SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid): void {
                    $db->query("INSERT IGNORE INTO `player_crafts` (xuid, blocked_crafts, unlocked_crafts) VALUES ('$xuid', '[]', '[]')");
                    $query = $db->query("SELECT * FROM player_crafts WHERE xuid = '$xuid';");
                    $result = $query->fetch_all(MYSQLI_ASSOC);
                    $async->setResult($result);
                }, static function (RequestAsync $async) use ($player): void {
                    $result = $async->getResult();

                    Main::getInstance()->getCraftManager()->cache[$player->getXuid()] = [
                        "blocked_crafts" => [],
                        "unlocked_crafts" => []
                    ];




                    if (isset($result[0])) {
                        $blockedCraft = json_decode($result[0]['blocked_crafts'], true);
                        $unlockedCraft = json_decode($result[0]['unlocked_crafts'], true);
                        Main::getInstance()->getCraftManager()->cache[$player->getXuid()]['blocked_crafts'] = $blockedCraft;
                        Main::getInstance()->getCraftManager()->cache[$player->getXuid()]['unlocked_crafts'] = $unlockedCraft;
                    }
                });
            }
        });
    }

    public function playerDataExistsAsync(CustomPlayer $player, callable $callback): void {
        $xuid = $player->getXuid();
        SQL::async(
            static function (AsyncTask $task, \mysqli $db) use ($xuid): void {
                $result = $db->query("SELECT xuid FROM `player_crafts` WHERE xuid = $xuid");
                $exists = $result->num_rows > 0;
                $task->setResult($exists);
            },
            static function (AsyncTask $task) use ($callback) {
                $callback($task->getResult());
            }
        );
    }


    public function blockCraftForPlayerAsync(Player $player, int $itemId): void {
        $xuid = $player->getXuid();
        SQL::async(
            static function (AsyncTask $task, \mysqli $db) use ($xuid, $itemId): void {
                // Récupérez la liste actuelle des crafts bloqués du joueur
                $result = $db->query("SELECT blocked_crafts FROM `player_crafts` WHERE xuid = '$xuid'")->fetch_all(MYSQLI_ASSOC);
                $blockedCrafts = $result ? json_decode($result[0]['blocked_crafts'] ?? [], true) : [];

                // Ajoutez l'item à la liste des crafts bloqués
                if (!in_array($itemId, $blockedCrafts)) {
                    $blockedCrafts[] = $itemId;
                }

                $blockedCrafts = json_encode($blockedCrafts);
                // Mettez à jour la base de données avec la nouvelle liste
                $db->query("UPDATE `player_crafts` SET blocked_crafts = '$blockedCrafts' WHERE xuid = '$xuid'");
            }
        );
    }

    // Méthode pour débloquer un craft dans la liste des crafts débloqués d'un joueur (de manière asynchrone)
    public function unlockCraftForPlayerAsync(Player $player, string $itemId, ?callable $server = null): void {
        $xuid = $player->getXuid();
        $this->cache[$player->getXuid()]['unlocked_crafts'][] = $itemId;
        SQL::async(
            static function (AsyncTask $task, \mysqli $db) use ($xuid, $itemId): void {
                // Récupérez la liste actuelle des crafts débloqués du joueur
                $result = $db->query("SELECT unlocked_crafts FROM `player_crafts` WHERE xuid = '$xuid'");

                if ($result->num_rows > 0) {
                    $result = $result->fetch_all(MYSQLI_ASSOC);
                    $unlockedCrafts = json_decode($result[0]['unlocked_crafts'], true);
                } else {
                    $unlockedCrafts = [];
                }

                // Ajoutez l'item à la liste des crafts débloqués
                if (!in_array($itemId, $unlockedCrafts)) {
                    $unlockedCrafts[] = $itemId;
                }

                $unlockedCrafts = json_encode($unlockedCrafts);
                // Mettez à jour la base de données avec la nouvelle liste
                $db->query("UPDATE `player_crafts` SET unlocked_crafts = '$unlockedCrafts' WHERE xuid = '$xuid'");
            },
            static function (AsyncTask $task) use ($server) {
                if ($server !== null) {
                    $server();
                }
            }
        );
    }

    // Méthode pour vérifier si un craft est bloqué pour un joueur (de manière asynchrone)
    public function isCraftBlockedForPlayerAsync(Player $player, int $itemId, callable $callback): void {
        $xuid = $player->getXuid();
        SQL::async(
            static function (AsyncTask $task, \mysqli $db) use ($xuid, $itemId, $callback): void {
                // Récupérez la liste des crafts bloqués du joueur
                $result = $db->query("SELECT blocked_crafts FROM `player_crafts` WHERE xuid = '$xuid'")->fetch_all(MYSQLI_ASSOC);
                $blockedCrafts = $result ? json_decode($result[0]['blocked_crafts'] ?? json_encode([]), true) : [];

                // Vérifiez si l'item est dans la liste des crafts bloqués
                $isBlocked = in_array($itemId, $blockedCrafts);

                // Appel du callback avec le résultat
                $callback($isBlocked);
            }
        );
    }

    // Méthode pour vérifier si un craft est débloqué pour un joueur (de manière asynchrone)
    public function isCraftUnlockedForPlayerAsync(Player $player, int $itemId, callable $callback): void {
        $xuid = $player->getXuid();
        SQL::async(
            static function (AsyncTask $task, \mysqli $db) use ($xuid, $itemId, $callback): void {
                // Récupérez la liste des crafts débloqués du joueur
                $result = $db->query("SELECT unlocked_crafts FROM `player_crafts` WHERE xuid = '$xuid'")->fetch_all(MYSQLI_ASSOC);
                $unlockedCrafts = $result ? json_decode($result[0]['unlocked_crafts'] ?? json_encode([]), true) : [];

                // Vérifiez si l'item est dans la liste des crafts débloqués
                $isUnlocked = in_array($itemId, $unlockedCrafts);

            }
        );
    }


    public function isCraftUnlocked(Player $player, string $itemId): bool {
        $xuid = $player->getXuid();
        $cache = $this->cache[$xuid] ?? null;


        if (isset($cache['unlocked_crafts']) && in_array($itemId, $cache['unlocked_crafts'])) {
            return true;
        } else return false;
    }
}

