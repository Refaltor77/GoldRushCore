<?php

namespace core\commands\executors\secret;

use core\commands\Executor;
use core\items\backpacks\BackpackFarm;
use core\items\backpacks\BackpackFossil;
use core\items\backpacks\BackpackOre;
use core\items\fossils\FossilDiplodocus;
use core\items\fossils\FossilNodosaurus;
use core\items\fossils\FossilPterodactyle;
use core\items\fossils\Fossils;
use core\items\fossils\FossilsBrachiosaurus;
use core\items\fossils\FossilSpinosaure;
use core\items\fossils\FossilStegosaurus;
use core\items\fossils\FossilTriceratops;
use core\items\fossils\FossilTyrannosaureRex;
use core\items\fossils\FossilVelociraptor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\data\bedrock\LegacyToStringIdMap;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\convert\ItemTypeDictionaryFromDataHelper;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class VendeurSac extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'vendeur_sac', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        /** @var BackpackOre|BackpackFarm $itemInHand */
        $itemInHand = $sender->getInventory()->getItemInHand();

        if (!in_array($itemInHand::class, [
            BackpackFarm::class,
            BackpackOre::class,
            BackpackFossil::class
        ])) {
            $sender->sendMessage("§6[§fVendeur§6] §cSalut, mon ami ! J'achète uniquement le contenu des sacs ; tu peux ranger tes objets, ça ne m'intéresse pas.");
            $this->sendErrorSound($sender);
            return;
        }

        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $sell = $config->get('shop');

        switch ($itemInHand::class) {
            case BackpackFarm::class:
                $item  = $itemInHand->getItemInStock();
                if ($item->isNull()){
                    $sender->sendMessage(Messages::message("§cVous pouvez pas vendre de l'air."));
                    return;
                }


                $total = 0;
                foreach ($sell['Farming']['items'] as $index => $values) {
                    $id = $values['idMeta'];
                    if (str_contains("goldrush:", $id)) {
                        $itemCheck = CustomiesItemFactory::getInstance()->get($id);
                    } else {
                        $itemCheck = StringToItemParser::getInstance()->parse($id);
                    }

                    if (!is_null($itemCheck)) {
                        if ($item->getTypeId() === $itemCheck->getTypeId()) {
                            $money = $itemInHand->getCountCustom() * $values['sell'];
                            $total += $money;
                            break;
                        }
                    }
                }

                if ($total > 0) {
                    Main::getInstance()->getEconomyManager()->addMoney($sender, $total);
                    $itemInHand->resetItem();
                    $this->sendSuccessSound($sender);
                    $sender->sendMessage("§6[§fVendeur§6]§f Merci pour tes cultures, je te donne §6" . $total . "$");
                } else {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage("§6[§fVendeur§6]§f Ton sac est vide !");
                }

                $sender->getInventory()->setItemInHand($itemInHand);
                break;
            case BackpackFossil::class:
                $tags = $itemInHand->getNamedTag();
                $fossil_diplodocus = $tags->getInt("fossil_diplodocus");
                $fossil_nodosaurus = $tags->getInt("fossil_nodosaurus");
                $fossil_pterodactyle = $tags->getInt("fossil_pterodactyle");
                $fossils = $tags->getInt("fossils");
                $fossil_brachiosaurus = $tags->getInt("fossil_brachiosaurus");
                $fossil_spinosaure = $tags->getInt("fossil_spinosaure");
                $fossil_stegosaurus = $tags->getInt("fossil_stegosaurus");
                $fossil_triceratops = $tags->getInt("fossil_triceratops");
                $fossil_tyrannosaure_rex = $tags->getInt("fossil_tyrannosaure_rex");
                $fossil_velociraptor = $tags->getInt("fossil_velociraptor");
                $itemInHand->resetSac();

                $total = 0;
                $total += (int)$fossil_diplodocus * 250;
                $total += (int)$fossil_nodosaurus * 250;
                $total += (int)$fossil_pterodactyle * 250;
                $total += (int)$fossils * 100;
                $total += (int)$fossil_brachiosaurus * 300;
                $total += (int)$fossil_stegosaurus * 300;
                $total += (int)$fossil_triceratops * 450;
                $total += (int)$fossil_tyrannosaure_rex * 500;
                $total += (int)$fossil_velociraptor * 600;

                if ($total > 0) {
                    Main::getInstance()->getEconomyManager()->addMoney($sender, $total);


                    $this->sendSuccessSound($sender);
                    $sender->sendMessage("§6[§fVendeur§6]§f Merci pour tes fossils, je te donne §6" . $total . "$");
                } else {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage("§6[§fVendeur§6]§f Ton sac est vide !");
                }

                $sender->getInventory()->setItemInHand($itemInHand);
                break;
            case BackpackOre::class:
                $tags = $itemInHand->getNamedTag();
                $coal = $tags->getInt("coal");
                $iron = $tags->getInt("iron");
                $diamond = $tags->getInt("diamond");
                $redstone = $tags->getInt("redstone");
                $copper = $tags->getInt("copper");
                $emerald = $tags->getInt("emerald");
                $amethyst = $tags->getInt("amethyst");
                $platine = $tags->getInt("platine");
                $gold = $tags->getInt("gold");
                $lapis = $tags->getInt("lapis");


                $total = 0;
                foreach ($sell['Minage']['items'] as $index => $values) {
                    $id = $values['idMeta'];
                    if (str_contains("goldrush:", $id)) {
                        $item = CustomiesItemFactory::getInstance()->get($id);
                    } else {
                        $item = StringToItemParser::getInstance()->parse($id);
                    }

                    if (!is_null($item)) {

                        if ($item->getTypeId() === VanillaItems::COAL()->getTypeId()) {
                            $money = $coal * $values['sell'];
                            $total += $money;
                            $tags->setInt("coal", 0);
                        }

                        if ($item->getTypeId() === VanillaItems::LAPIS_LAZULI()->getTypeId()) {
                            $money = $lapis * $values['sell'];
                            $total += $money;
                            $tags->setInt("lapis", 0);
                        }

                        if ($item->getTypeId() === VanillaItems::RAW_IRON()->getTypeId()) {
                            $money = $iron * $values['sell'];
                            $total += $money;
                            $tags->setInt("iron", 0);

                        }

                        if ($item->getTypeId() === VanillaItems::DIAMOND()->getTypeId()) {
                            $money = $diamond * $values['sell'];
                            $total += $money;
                            $tags->setInt("diamond", 0);

                        }


                        if ($item->getTypeId() === VanillaItems::REDSTONE_DUST()->getTypeId()) {
                            $money = $redstone * $values['sell'];
                            $total += $money;
                            $tags->setInt("redstone", 0);

                        }

                        if ($item->getTypeId() === CustomiesItemFactory::getInstance()->get(Ids::COPPER_RAW)->getTypeId()) {
                            $money = $copper * $values['sell'];
                            $total += $money;
                            $tags->setInt("copper", 0);

                        }

                        if ($item->getTypeId() === CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)->getTypeId()) {
                            $money = $emerald * $values['sell'];
                            $total += $money;
                            $tags->setInt("emerald", 0);

                        }

                        if ($item->getTypeId() === CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)->getTypeId()) {
                            $money = $amethyst * $values['sell'];
                            $total += $money;
                            $tags->setInt("amethyst", 0);

                        }

                        if ($item->getTypeId() === CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER)->getTypeId()) {
                            $money = $gold * $values['sell'];
                            $total += $money;

                            $tags->setInt("gold", 0);

                        }

                        if ($item->getTypeId() === CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_RAW)->getTypeId()) {
                            $money = $platine * $values['sell'];
                            $total += $money;

                            $tags->setInt("platine", 0);

                        }
                    }
                }

                if ($total > 0) {
                    Main::getInstance()->getEconomyManager()->addMoney($sender, $total);


                    $this->sendSuccessSound($sender);
                    $sender->sendMessage("§6[§fVendeur§6]§f Merci pour tes minerais, je te donne §6" . $total . "$");
                } else {
                    $this->sendErrorSound($sender);
                    $sender->sendMessage("§6[§fVendeur§6]§f Ton sac est vide !");
                }

                $sender->getInventory()->setItemInHand($itemInHand);
                break;
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}