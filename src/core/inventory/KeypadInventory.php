<?php

namespace core\inventory;


use core\items\others\keypad\Eight;
use core\items\others\keypad\Five;
use core\items\others\keypad\Four;
use core\items\others\keypad\Nine;
use core\items\others\keypad\One;
use core\items\others\keypad\Seven;
use core\items\others\keypad\Six;
use core\items\others\keypad\Three;
use core\items\others\keypad\Two;
use core\items\others\keypad\Zero;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use pocketmine\world\sound\PopSound;
use tedo0627\inventoryui\CustomInventory;

class KeypadInventory extends CustomInventory {

    private $acceptCallback = "";

    public function __construct(string $title = "keypad", ?int $verticalLength = null)
    {
        parent::__construct(60, $title, $verticalLength);

        $this->setItem(22, CustomiesItemFactory::getInstance()->get(Ids::SEVEN));
        $this->setItem(23, CustomiesItemFactory::getInstance()->get(Ids::EIGHT));
        $this->setItem(24, CustomiesItemFactory::getInstance()->get(Ids::NINE));

        $this->setItem(32, CustomiesItemFactory::getInstance()->get(Ids::FOUR));
        $this->setItem(33, CustomiesItemFactory::getInstance()->get(Ids::FIVE));
        $this->setItem(34, CustomiesItemFactory::getInstance()->get(Ids::SIX));

        $this->setItem(42, CustomiesItemFactory::getInstance()->get(Ids::ONE));
        $this->setItem(43, CustomiesItemFactory::getInstance()->get(Ids::TWO));
        $this->setItem(44, CustomiesItemFactory::getInstance()->get(Ids::THREE));

        $this->setItem(54, CustomiesItemFactory::getInstance()->get(Ids::ACCEPT));
        $this->setItem(53, CustomiesItemFactory::getInstance()->get(Ids::ZERO));
        $this->setItem(52, CustomiesItemFactory::getInstance()->get(Ids::REFUS));
    }

    public function onClose(Player $who): void
    {
        parent::onClose($who);
    }
    public function setAcceptCallback(callable $callback): void {
        $this->acceptCallback = $callback;
    }
    public function click(Player $player, int $slot, Item $sourceItem, Item $targetItem): bool
    {
        $numberSlots = [22, 23, 24, 32, 33, 34, 42, 43, 44, 25, 53];
        $slotsAcceptOrDeny = [54, 52];
        $slotInputNumber = [2, 3, 4, 5];

        $slotInputOne = $this->getItem(2);
        $slotInputTwo = $this->getItem(3);
        $slotInputThree = $this->getItem(4);
        $slotInputFour = $this->getItem(5);

        if (in_array($slot, $numberSlots)) {
            if ($slotInputOne->isNull()) {
                $this->setItem(2, $this->getItem($slot));
                $player->getWorld()->addSound($player->getEyePos(), new NoteSound(NoteInstrument::IRON_XYLOPHONE(), 4), [$player]);
            } elseif ($slotInputTwo->isNull()) {
                $this->setItem(3, $this->getItem($slot));
                $player->getWorld()->addSound($player->getEyePos(), new NoteSound(NoteInstrument::IRON_XYLOPHONE(), 4), [$player]);
            } elseif ($slotInputThree->isNull()) {
                $this->setItem(4, $this->getItem($slot));
                $player->getWorld()->addSound($player->getEyePos(), new NoteSound(NoteInstrument::IRON_XYLOPHONE(), 4), [$player]);
            }elseif ($slotInputFour->isNull()) {
                $this->setItem(5, $this->getItem($slot));
                $player->getWorld()->addSound($player->getEyePos(), new NoteSound(NoteInstrument::IRON_XYLOPHONE(), 4), [$player]);
            } else {
                $player->getWorld()->addSound($player->getEyePos(), new NoteSound(NoteInstrument::BASS_DRUM(), 1), [$player]);
            }
        }

        if ($slot === 54) {
            if (!$slotInputOne->isNull() && !$slotInputTwo->isNull() && !$slotInputThree->isNull() && !$slotInputFour->isNull()) {
                $call = $this->acceptCallback;

                $arrayConversion = [
                    Zero::class => "0",
                    One::class => "1",
                    Two::class => "2",
                    Three::class => "3",
                    Four::class => "4",
                    Five::class => "5",
                    Six::class => "6",
                    Seven::class => "7",
                    Eight::class => "8",
                    Nine::class => "9"
                ];

                $one = $arrayConversion[$slotInputOne::class];
                $two = $arrayConversion[$slotInputTwo::class];
                $three = $arrayConversion[$slotInputThree::class];
                $four = $arrayConversion[$slotInputFour::class];
                $call($one . $two . $three . $four);
            } else {
                $player->sendErrorSound();
            }



        } elseif ($slot === 52) {
            if (!$slotInputOne->isNull() && $slotInputTwo->isNull() && $slotInputThree->isNull() && $slotInputFour->isNull()) {
                $this->setItem(2, VanillaItems::AIR());
                $player->getWorld()->addSound($player->getEyePos(), new PopSound(), [$player]);
            } elseif (!$slotInputTwo->isNull() && $slotInputThree->isNull() && $slotInputFour->isNull()) {
                $this->setItem(3, VanillaItems::AIR());
                $player->getWorld()->addSound($player->getEyePos(), new PopSound(), [$player]);
            } elseif (!$slotInputThree->isNull() && $slotInputFour->isNull()) {
                $this->setItem(4, VanillaItems::AIR());
                $player->getWorld()->addSound($player->getEyePos(), new PopSound(), [$player]);
            }elseif (!$slotInputFour->isNull()) {
                $this->setItem(5, VanillaItems::AIR());
                $player->getWorld()->addSound($player->getEyePos(), new PopSound(), [$player]);
            } else {
                $player->sendErrorSound();
            }
        }

        return true;
    }

    public function onOpen(Player $who): void
    {
        $who->sendErrorSound();
        parent::onOpen($who);
    }
}