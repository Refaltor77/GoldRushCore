<?php

namespace core\utils;

use core\Main;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\component\DiggerComponent;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\OnScreenTextureAnimationPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use const pocketmine\BEDROCK_DATA_PATH;

class Utils
{
    public static function AminationTexture(Player $player, int $effectId){
        $packet = new OnScreenTextureAnimationPacket();
        $packet->effectId = $effectId;
        $player->getNetworkSession()->sendDataPacket($packet);
    }

    public static function getDiggerComponent(Durable $item, int $speed): ?DiggerComponent {
        $blocks = VanillaBlocks::getAll();
        $blocksCustom = CustomiesBlockFactory::getInstance()->getAll();

        $component = new DiggerComponent();
        $found = false;

        foreach ($blocks as $constant => $block) {
            if ($block->getBreakInfo()->getToolType() == $item->getBlockToolType()) {
                $found = true;
                $component->withBlocks($speed, $block);
            }
        }

        foreach ($blocksCustom as  $block) {
            if ($block->getBreakInfo()->getToolType() == $item->getBlockToolType()) {
                $found = true;
                $component->withBlocks($speed, $block);
            }
        }

        return ($found === true ? $component : null);
    }

    public static function timeout(callable $code, int $tick): void {
        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($code), $tick);
    }

    public static function center(Vector3 $vector3) : Vector3 {
        return $vector3->add(0.5, 0.5, 0.5);
    }

    public static function moneyFormat(int $nombre): string {
        $suffixes = array('', 'k', 'm', 'b', 't');

        $index = 0;
        while ($nombre >= 1000 && $index < count($suffixes) - 1) {
            $nombre /= 1000;
            $index++;
        }

        return (intval($nombre) % 1 !== 0) ? sprintf("%.1f%s", intval($nombre), $suffixes[$index]) : sprintf("%d%s", $nombre, $suffixes[$index]);
    }

    public static function getResourceFile(string $file): string
    {
        return str_replace(["\\utils", "/utils"], DIRECTORY_SEPARATOR . "resources", __DIR__) . DIRECTORY_SEPARATOR . $file;
    }

    public static function callDirectory(string $directory, callable $callable): void
    {
        $main = explode("\\", Main::getInstance()->getDescription()->getMain());
        unset($main[array_key_last($main)]);
        $main = implode("/", $main);
        $directory = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $directory), "/");
        $dir = Main::getInstance()->file . "src/$main/" . $directory;


        foreach (array_diff(scandir($dir), [".", ".."]) as $file) {
            $path = $dir . "/$file";
            $extension = pathinfo($path)["extension"] ?? null;

            if ($extension === null) {
                self::callDirectory($directory . "/" . $file, $callable);
            } elseif ($extension === "php") {
                $namespaceDirectory = str_replace("/", "\\", $directory);
                $namespaceMain = str_replace("/", "\\", $main);
                $namespace = $namespaceMain . "\\$namespaceDirectory\\" . basename($file, ".php");
                $callable($namespace);
            }
        }
    }

    /**
     * @return int[]
     */
    public static function getBlockIdsMap(): array
    {
        $file = "block_id_map.json";
        return array_merge(json_decode(file_get_contents(BEDROCK_DATA_PATH . "/$file"), true), json_decode(file_get_contents(self::getResourceFile($file)), true));
    }

    /**
     * @return int[]
     */
    public static function getItemIdsMap(): array
    {
        $file = "item_id_map.json";
        return array_merge(json_decode(file_get_contents(BEDROCK_DATA_PATH . "/$file"), true), json_decode(file_get_contents(self::getResourceFile($file)), true));
    }

    public static function clamp(int|float $value, int|float $min, int|float $max): int|float
    {
        if ($value < $min) return $min;
        if ($value > $max) return $max;
        return $value;
    }

    public static function serilizeItem(Item $item): string
    {
        $nbt = $item->nbtSerialize();
        return base64_encode(serialize($nbt));
    }

    public static function unserializeItem(string $serialize): Item {
        return Item::nbtDeserialize(unserialize(base64_decode($serialize)));
    }
}