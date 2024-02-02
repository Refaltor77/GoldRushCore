<?php

namespace core\entities;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\entity\Human;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\item\Durable;
use pocketmine\player\Player;

class OrbEntity extends ExperienceOrb
{
    protected function entityBaseTick(int $tickDiff = 1) : bool{
        $hasUpdate = parent::entityBaseTick($tickDiff);


        $currentTarget = $this->getTargetPlayer();
        if($currentTarget !== null && (!$currentTarget->isAlive() || !$currentTarget->getXpManager()->canAttractXpOrbs() || $currentTarget->location->distanceSquared($this->location) > self::MAX_TARGET_DISTANCE ** 2)){
            $currentTarget = null;
        }

        if ($currentTarget !== null) {
            if($currentTarget->getXpManager()->canPickupXp() && $this->boundingBox->intersectsWith($currentTarget->getBoundingBox())){
                $this->flagForDespawn();

                $currentTarget->getXpManager()->onPickupXp($this->getXpValue());



                $mainHandIndex = -1;
                $offHandIndex = -2;

                $equipment = [];

                if(($item = $currentTarget->getInventory()->getItemInHand()) instanceof Durable && $item->hasEnchantment(VanillaEnchantments::MENDING())){
                    $equipment[$mainHandIndex] = $item;
                }
                if(($item = $currentTarget->getOffHandInventory()->getItem(0)) instanceof Durable && $item->hasEnchantment(VanillaEnchantments::MENDING())){
                    $equipment[$offHandIndex] = $item;
                }
                foreach($currentTarget->getArmorInventory()->getContents() as $k => $armorItem){
                    if($armorItem instanceof Durable && $armorItem->hasEnchantment(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::MENDING))){
                        $equipment[$k] = $armorItem;
                    }
                }


                $xpValue = $this->getXpValue();
                if(count($equipment) > 0){
                    $repairItem = $equipment[$k = array_rand($equipment)];
                    if($repairItem->getDamage() > 0){
                        $repairAmount = min($repairItem->getDamage(), $xpValue * 2);
                        $repairItem->setDamage($repairItem->getDamage() - $repairAmount);
                        $xpValue -= (int) ceil($repairAmount / 2);

                        if($k === $mainHandIndex){
                            $currentTarget->getInventory()->setItemInHand($repairItem);
                        }elseif($k === $offHandIndex){
                            $currentTarget->getOffHandInventory()->setItem(0, $repairItem);
                        }else{
                            $currentTarget->getArmorInventory()->setItem($k, $repairItem);
                        }
                    }
                }

            }
        }


        return $hasUpdate;
    }
}