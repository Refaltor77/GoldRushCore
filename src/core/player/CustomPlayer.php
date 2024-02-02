<?php

namespace core\player;

use anticheat\tasks\ChunkRequestTask;
use core\commands\executors\ItemGlass;
use core\commands\executors\staff\Vanish;
use core\enchantments\VanillaEnchantment;
use core\entities\BossSouls;
use core\entities\ItemEntitySafe;
use core\events\BossBarReloadEvent;
use core\items\armors\others\HoodHelmet;
use corepvp\items\armors\spectral\SpectraleBoots;
use corepvp\items\armors\spectral\SpectraleChestplate;
use corepvp\items\armors\spectral\SpectraleHelmet;
use corepvp\items\armors\spectral\SpectraleLeggings;
use core\items\unclaimFinders\UnclaimFinderAmethyst;
use core\items\unclaimFinders\UnclaimFinderCopper;
use core\items\unclaimFinders\UnclaimFinderEmerald;
use core\items\unclaimFinders\UnclaimFinderGold;
use core\items\unclaimFinders\UnclaimFinderPlatine;
use corepvp\listeners\types\armor\ArmorEffect;
use core\Main;
use core\managers\Stats;
use core\managers\stats\StatsManager;
use core\messages\Messages;
use core\settings\Ids;
use core\timings\BossBarTiming;
use core\timings\ChestFinderTiming;
use core\timings\NametagTiming;
use core\timings\TpsTimingStatus;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\block\Torch;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerPostChunkSendEvent;
use pocketmine\form\Form;
use pocketmine\inventory\Inventory;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\cache\ChunkCache;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\PlayerFogPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\network\mcpe\protocol\UnlockedRecipesPacket;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\player\UsedChunkStatus;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\Utils;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\GhastShootSound;
use pocketmine\world\sound\GhastSound;
use pocketmine\world\sound\ItemBreakSound;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpLevelUpSound;
use pocketmine\world\World;
use ReflectionProperty;

class CustomPlayer extends Player
{

    public bool $quit = false;
    public bool $hasReallyConnected = false;
    public bool $hasTagged = false;
    public int $taggedTime = 0;
    public int $hdv = 0;
    public bool $hasSendHdv = false;
    public ?Block $lastblock = null;
    protected ?SurvivalBlockBreakHandler $blockBreakHandlerCustom = null;
    private BossBarTiming $bossBarTiming;
    private ChestFinderTiming $chestFinderTiming;
    private NametagTiming $nametagTiming;
    private TpsTimingStatus $tpsTiming;
    private bool $flash = true;
    private ?int $spectralArmorCooldown = null;
    public bool $hasFreeze = false;
    public int $timeBoxOpen = 0;
    public bool $isInCinematic = false;
    public bool $showStatus = false;


    public bool $hasJobsLoaded = false;
    public bool $hasInvLoaded = false;
    public bool $hasRankLoaded = false;
    public bool $hasStatsLoaded = false;
    public bool $hasDataLoaded = false;
    public bool $hasHomeLoaded = false;
    public bool $hasEconomyLoaded = false;
    public bool $hasEnderLoaded = false;
    public bool $hasJobsStorageLoaded = false;
    public bool $hasQuestStorageLoaded = false;
    public bool $hasSettingsLoaded = false;
    public bool $hasSoreboardLoaded = false;
    public bool $hasPrimeLoaded = false;
    public bool $hasPlayer = true;

    public int $resetFallTick = 0;


    public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag)
    {
        parent::__construct($server, $session, $playerInfo, $authenticated, $spawnLocation, $namedtag);
        $this->bossBarTiming = BossBarTiming::start();
        $this->chestFinderTiming = ChestFinderTiming::start();
        $this->nametagTiming = NametagTiming::start();
        $this->tpsTiming = TpsTimingStatus::start();
    }

    public function isOp(): bool {
        return Server::getInstance()->isOp($this->getName());
    }

    public function resetFallDistance(): void
    {
        parent::resetFallDistance();
        $this->resetFallTick = 20 * 5;
    }


    public function continueBreakBlock(Vector3 $pos, int $face): void
    {
        if($this->blockBreakHandler !== null && $this->blockBreakHandler->getBlockPos()->distanceSquared($pos) < 0.0001){
            $this->blockBreakHandler->setTargetedFace($face);
            if (($this->blockBreakHandler->getBreakProgress() + $this->blockBreakHandler->getBreakSpeed()) >= 0.93) {
                $this->breakBlock($this->blockBreakHandler->getBlockPos());
            }
        }
    }

    public function setCurrentWindow(Inventory $inventory): bool
    {
        if ($this->hasFreeze()) return false;
        if ($this->getCurrentWindow() === null) {
            return parent::setCurrentWindow($inventory);
        }
        return false;
    }


    public function getParticlePos(): Position {
        return new Position(
            $this->getPosition()->getFloorX() + 0.5,
            $this->getPosition()->getFloorY() + 1,
            $this->getPosition()->getFloorZ() + 0.8,
        $this->getPosition()->getWorld()
        );
    }


    const NOTIF_TYPE_BASIC = 'basic';
    const NOTIF_TYPE_MONEY = 'money';
    const NOTIF_TYPE_EMAIL = 'email';

    const NOTIF_TYPE_MINEUR = 'mineur';
    const NOTIF_TYPE_HUNTER = 'hunter';
    const NOTIF_TYPE_BUCHERON = 'bucheron';
    const NOTIF_TYPE_FARMER = 'farmer';

    public function sendNotification(string $text, string $type = self::NOTIF_TYPE_BASIC): void {
        $this->sendTitle("notif_" . $type, $text);
        $this->getWorld()->addSound($this->getEyePos(), new PopSound(), [$this]);
    }

    public function sendCustomNotification(string $text, string $texture): void {
        $this->sendTitle("notif_custom_" . $texture, $text);
        $this->getWorld()->addSound($this->getEyePos(), new PopSound(), [$this]);
    }

    public function getNetworkSession(): NetworkSession
    {
        return parent::getNetworkSession(); // TODO: Change the autogenerated stub
    }

    public function getDrops(): array
    {
        if($this->hasFiniteResources()){
            return array_filter(array_merge(
                array_values($this->inventory->getContents()),
                array_values($this->armorInventory->getContents()),
            ), function(Item $item) : bool{ return !$item->hasEnchantment(VanillaEnchantments::VANISHING()) && !$item->keepOnDeath(); });
        }
        return [];
    }

    public function attackEntity(Entity $entity): bool
    {
        return parent::attackEntity($entity);
    }

    public function sendForm(Form $form): void
    {
        if ($this->isConnected()) {
            $this->sendMessage(Messages::message("§fPréparation du formulaire..."));
            \core\utils\Utils::timeout(function () use ($form) : void {
                if ($this->isConnected()) {
                    $id = $this->formIdCounter++;
                    if($this->getNetworkSession()->onFormSent($id, $form)){
                        $this->forms[$id] = $form;
                    }
                }
            }, 3);
        }
    }



    public function sendErrorSound(): void {
        $this->getWorld()->addSound($this->getEyePos(), new NoteSound(NoteInstrument::BASS_DRUM(), 7), [$this]);
    }

    public function sendGhastPeur(): void {
        $this->getWorld()->addSound($this->getEyePos(), new GhastShootSound(), [$this]);
        $this->getWorld()->addSound($this->getEyePos(), new GhastSound(), [$this]);
    }

    public function sendSuccessSound(): void
    {
        $this->getWorld()->addSound($this->getEyePos(), new XpLevelUpSound(5), [$this]);
    }

    public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null): bool
    {

        $oldWorld = $this->getWorld()->getFolderName();
        $a =  parent::teleport($pos, $yaw, $pitch);
        if ($oldWorld === "crystal_maudit") {
            if ($oldWorld !== $this->getWorld()->getFolderName()) {
                $fog = PlayerFogPacket::create([
                    'minecraft:fog_default',
                ]);

                $pk = new StopSoundPacket();
                $pk->soundName = "music.peste.jump";
                $pk->stopAll = true;
                $this->getNetworkSession()->sendDataPacket($pk, true);
                $this->getNetworkSession()->sendDataPacket($fog);
            }
        }

        return $a;
    }


    public function sendPop(): void
    {
        $this->getWorld()->addSound($this->getEyePos(), new PopSound(), [$this]);
    }




    public function checkupBug(): void {
        foreach ($this->getInventory()->getContents() as $slot => $item) {
            if (in_array($item->getTypeId(), [
                ItemTypeIds::GOLD_NUGGET,
                ItemTypeIds::GOLD_INGOT
            ])) {
                $this->getInventory()->setItem($slot, VanillaItems::STICK());
            }
        }
    }

    private bool $spectral = false;

    public function onUpdate(int $currentTick): bool
    {

        if ($this->resetFallTick !== 0) {
            $this->setFallDistance(0);
        }
        $this->resetFallTick--;

        if ($this->hasTagged()) {
            if ($this->taggedTime <= time()) {
                $this->setTagged(false);
                $this->sendNotification("§aVous êtes désormais plus en combat !", self::NOTIF_TYPE_HUNTER);
            }
        }

        if ($this->getArmorInventory()->getHelmet() instanceof HoodHelmet) {
            $this->setNameTagAlwaysVisible(false);
            $this->setNameTagVisible(false);
        } else {
            $this->setNameTagAlwaysVisible(true);
            $this->setNameTagVisible(true);
        }


        $headHelmet = $this->getArmorInventory()->getHelmet();
        if ($headHelmet->getStateId() !== VanillaItems::AIR()->getStateId() && $headHelmet->getStateId() === CustomiesItemFactory::getInstance()->get(Ids::HOOD_HELMET)->getStateId()) {
            $this->nameTagVisible = false;
        } else {
            $this->nameTagVisible = true;
        }

        $spectralArmor = false;

        $spectralArmors = [
            CustomiesItemFactory::getInstance()->get(Ids::SPECTRAL_HELMET)->getStateId(),
            CustomiesItemFactory::getInstance()->get(Ids::SPECTRAL_CHESTPLATE)->getStateId(),
            CustomiesItemFactory::getInstance()->get(Ids::SPECTRAL_LEGGINGS)->getStateId(),
            CustomiesItemFactory::getInstance()->get(Ids::SPECTRAL_BOOTS)->getStateId()
        ];




        # spectral caclcul
        $armor = $this->getArmorInventory();

        $helmet = $armor->getHelmet();
        $chestplate = $armor->getChestplate();
        $leggings = $armor->getLeggings();
        $boots = $armor->getBoots();

        $baseMaxHeal = 20;
        $heartRemove = 0;
        if ($helmet::class === SpectraleHelmet::class) {
            $heartRemove += 4;
        }
        if ($chestplate::class === SpectraleChestplate::class) {
            $heartRemove += 4;
        }
        if ($leggings::class === SpectraleLeggings::class) {
            $heartRemove += 4;
        }
        if ($boots::class === SpectraleBoots::class) {
            $heartRemove += 4;
        }
        $baseMaxHeal = $baseMaxHeal - $heartRemove;
        $this->setMaxHealth($baseMaxHeal);



        foreach ($this->getArmorInventory()->getContents() as $item) {
            if ($item instanceof Armor) {
                $slot = $item->getArmorSlot();
                $sourceItem = $this->getArmorInventory()->getItem($slot);
                if (in_array($sourceItem->getStateId(), $spectralArmors)) {
                    if (!$this->getEffects()->has(VanillaEffects::INVISIBILITY())) {
                        $spectralArmor = true;
                        break;
                    }
                }
            }
        }


        if (!is_null($this->spectralArmorCooldown)) {
            if ($this->spectralArmorCooldown <= time()) {
                $this->spectralArmorCooldown = null;
                foreach ($this->getArmorInventory()->getContents() as $targetItem) {
                    if ($targetItem instanceof Armor) {
                        $slot = $targetItem->getArmorSlot();
                        $sourceItem = $this->getArmorInventory()->getItem($slot);

                        ArmorEffect::addEffects($this, $sourceItem, $targetItem);
                    } else {
                        if ($targetItem->isNull()) {
                            ArmorEffect::addEffects($this, VanillaBlocks::AIR()->asItem(), $targetItem);
                        }
                    }
                }
            }
        }

        if ($spectralArmor) {
            if (is_null($this->spectralArmorCooldown)) $this->spectralArmorCooldown = time() + 30;

            if ($currentTick % 20 === 0) {
                $this->flash = $this->flash($this->flash);
            }
        }

        $this->bossBarTiming->update();
        $this->bossBarTiming->execute(function (): void {
            (new BossBarReloadEvent($this))->call();
            Main::getInstance()->getStatsManager()->addValue($this->getXuid(), StatsManager::TIME);
        });
        $this->tpsTiming->update();
        $this->tpsTiming->execute(function (): void {
            if ($this->showStatus) {
                $this->sendTip("§6- §fStatus du Serveur §6-\n§6TPS: §f" . $this->getServer()->getTicksPerSecond() . "\n§6CPU USAGE§f: " . $this->getServer()->getTickUsage());
            }
        });


        $this->chestFinderTiming->update();
        $this->chestFinderTiming->execute(function (): void {
            $unclaimClass = [
                UnclaimFinderCopper::class,
                UnclaimFinderEmerald::class,
                UnclaimFinderAmethyst::class,
                UnclaimFinderPlatine::class,
                UnclaimFinderGold::class
            ];

            $item = $this->getInventory()->getItemInHand();
            if (in_array($item::class, $unclaimClass)) {
                $item->useItem($this);
            }
        });

        $this->nametagTiming->update();
        $this->nametagTiming->execute(function (): void {
            $rank = Main::getInstance()->getRankManager()->getSupremeRankPriorityChat($this->getXuid());
            $unicode = Main::getInstance()->getRankManager()->getUnicode($rank);

            if (in_array($this->getXuid(), Vanish::$inVanish)) {
                $this->setNameTag($unicode . " " . $this->getName() . "[VANISH]");
            } else {
                $this->setNameTag($unicode . " " . $this->getName());
            }
        });



        if ($this->getWorld()->getFolderName() === "spectral" && !$this->spectral) {
            $pk = PlayerFogPacket::create([
                "goldrush:spectral_fogs"
            ]);
            $this->getNetworkSession()->sendDataPacket($pk);
            $this->spectral = true;
        } else {
            if ($this->getWorld()->getFolderName() !== "spectral" && $this->spectral) {
                $this->spectral = false;
            }
        }


        return parent::onUpdate($currentTick);
    }

    public function hasTagged(): bool
    {
        return $this->hasTagged;
    }

    public function setTagged(bool $tagged): void
    {
        if ($tagged) {
            $this->hasTagged = true;
            $this->taggedTime = time() + 30;
        } else {
            $this->hasTagged = false;
            $this->taggedTime = 0;
        }
    }

    private function flash(bool $flash): bool
    {
        if ($flash) {
            $this->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 20, 0, false));
        } else {
            $this->getEffects()->remove(VanillaEffects::INVISIBILITY());
        }

        return !$flash;
    }

    public function sendMessage(Translatable|string $message, bool $prefixSend = false, string $prefix = "§l§6»§r", bool $success = true): void
    {
        if ($prefixSend) {
            if ($message instanceof Translatable) {
                if ($this->isConnected()) {
                    parent::sendMessage($message->prefix(($success ? "§r" : "§c") . $prefix . " "));
                }
            } else {
                $message = ($success ? "§r" : "§c") . $prefix . " " . $message;
                if ($this->isConnected()) parent::sendMessage($message);
            }
        } else {
            if ($this->isConnected()) {
                parent::sendMessage($message);
            }
        }
    }

    public function interactBlock(Vector3 $pos, int $face, Vector3 $clickOffset): bool
    {
        if (isset(ItemGlass::$isGlass[$this->getXuid()])) {
            $location = new Location($pos->getX() + 0.5, $pos->getY(), $pos->getZ() + 0.5, $this->getWorld(), 0, 0);
            (new ItemEntitySafe($location, $this->getInventory()->getItemInHand()))->spawnToAll();
            unset(ItemGlass::$isGlass[$this->getXuid()]);
        }
        return parent::interactBlock($pos, $face, $clickOffset);
    }

    protected function destroyCycles(): void
    {
        parent::destroyCycles();
        $this->blockBreakHandlerCustom = null;
    }

    protected function onDeath(): void
    {
        $pk = new StopSoundPacket();
        $pk->soundName = "music.boss_souls.fight";
        $pk->stopAll = true;
        $this->getNetworkSession()->sendDataPacket($pk);

        if (isset(BossSouls::$playersInBoss[$this->getXuid()])) {
            unset(BossSouls::$playersInBoss[$this->getXuid()]);
        }
        parent::onDeath();
    }


    public function setFreeze(bool $value): void {
        $this->hasFreeze = $value;
    }

    public function hasFreeze(): bool {return $this->hasFreeze;}


}