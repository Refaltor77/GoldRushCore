<?php

namespace core\crafts;

use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\FurnaceType;
use pocketmine\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;
use pocketmine\Server;

class Furnace
{
    public function init(): void {

        $array = [
            [
                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_RAW),
                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT),

            ],

            [
                CustomiesItemFactory::getInstance()->get(Ids::GOLD_RAW) ,
                CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT)
            ],

            [
                CustomiesItemFactory::getInstance()->get(Ids::COPPER_RAW) ,
                CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT)
            ],
        ];

        $manager = Server::getInstance()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::FURNACE());

        foreach ($array as $values)  {
            $furnaceRecipe = new FurnaceRecipe(
                $values[1], new ExactRecipeIngredient($values[0])
            );
            $manager->register($furnaceRecipe);
        }


        $manager = Server::getInstance()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::BLAST_FURNACE());

        foreach ($array as $values)  {
            $furnaceRecipe = new FurnaceRecipe(
                $values[1], new ExactRecipeIngredient($values[0])
            );
            $manager->register($furnaceRecipe);
        }
    }
}