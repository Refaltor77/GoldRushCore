<?php


namespace core\crafts;

use pocketmine\crafting\RecipeIngredient;
use pocketmine\item\Item;

class RecipeIngredientCustom implements RecipeIngredient
{
    public function __construct(private Item $item){
        if($item->getCount() !== 1){
            throw new \InvalidArgumentException("Recipe ingredients cannot require count");
        }
        $this->item = clone $item;
    }

    public function getItem() : Item{ return clone $this->item; }

    public function accepts(Item $item) : bool{
        //client-side, recipe inputs can't actually require NBT
        //but on the PM side, we currently check for it if the input requires it, so we have to continue to do so for
        //the sake of consistency
        return $item->getCount() >= 1 && $this->item->equals($item, true, $this->item->hasNamedTag());
    }

    public function __toString() : string{
        return "ExactRecipeIngredient(" . $this->item . ")";
    }
}