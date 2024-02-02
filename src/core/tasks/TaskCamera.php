<?php

namespace core\tasks;

use core\packets\CustomCameraInstructionPacket;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\CameraPresetsPacket;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEase;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEaseType;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionRotation;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class TaskCamera extends Task
{
    private int $secs = 0;

    public function __construct(public Player $player)
    {

    }

    public static function sendAllPresets(Player $player, Vector3 $camPosition): void
    {
        $array = [
            new CameraPreset("minecraft:third_person", "", null, null, null, null, null, 0, false),
            new CameraPreset("minecraft:free", "", $camPosition->getX(), $camPosition->getY(), $camPosition->getZ(), null, null, 0, true),
        ];
        $player->getNetworkSession()->sendDataPacket(CameraPresetsPacket::create($array));
    }

    public function onRun(): void
    {
        Server::getInstance()->broadcastMessage($this->secs);
        $player = $this->player;
        $posCam = $player->getPosition()->add(0, 50, 0);
        if ($this->secs === 0) {
            TaskCamera::sendFree($player);
            TaskCamera::sendFade($player, 0.4, 0.1, 0.1);
        }
        if ($this->secs === 4) {
            TaskCamera::sendCamPos($player, $posCam);
        }

        if ($this->secs === 40) {
            $posCam = $posCam->add(50, 0, 0);
            TaskCamera::sendCamPos($player, $posCam);
        }

        if ($this->secs === 60) {
            TaskCamera::sendClear($player);
        }
        $this->secs++;
    }

    public static function sendFree(Player $player): void
    {
        $array = [
            new CameraPreset("minecraft:third_person", "", null, null, null, null, null, 0, false),
            new CameraPreset("minecraft:third_person_front", "", null, null, null, null, null, 0, false),
            new CameraPreset("minecraft:first_person", "", null, null, null, null, null, 0, false),
            new CameraPreset("minecraft:free", "", null, null, null, null, null, 0, false),
        ];
        $player->getNetworkSession()->sendDataPacket(CameraPresetsPacket::create($array));
    }

    public static function sendFade(Player $player, float $r, float $g, float $b, float $fadeStartTime = 1, float $inFadeTime = 1, float $quitFadeTime = 1): void
    {
        $pk = CameraInstructionPacket::create(
            null,
            null,
            new CameraFadeInstruction(
                new CameraFadeInstructionTime(
                    $fadeStartTime, $inFadeTime, $quitFadeTime
                ),
                new CameraFadeInstructionColor($r, $g, $b)
            )
        );
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public static function sendCamPos(Player $player, Vector3 $camPositionInitial): void
    {
        $cam = new CameraSetInstruction(
            3,
            new CameraSetInstructionEase(
                CameraSetInstructionEaseType::LINEAR,
                10.0
            ),
            $camPositionInitial,
            new CameraSetInstructionRotation(0, 90),
            $player->getEyePos(),
            false
        );

        $pk = CameraInstructionPacket::create($cam, null, null);
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public static function sendClear(Player $player): void
    {
        $pk = CameraInstructionPacket::create(null, true, null);
        $player->getNetworkSession()->sendDataPacket($pk);
    }
}