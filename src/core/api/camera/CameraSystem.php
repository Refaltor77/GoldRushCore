<?php

namespace core\api\camera;

use core\Main;
use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\CameraPresetsPacket;
use pocketmine\network\mcpe\protocol\CameraShakePacket;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEase;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionRotation;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\Position;

class CameraSystem
{
    public int $seconds = 0;
    private Player $player;
    private ?TaskHandler $taskHandler = null;
    private Vector3 $positionCamera;

    public function __construct(Player $player)
    {
        $this->player = $player;
        $this->positionCamera = Vector3::zero();
        $this->sendAllPresets();
    }

    private function sendAllPresets(): void
    {
        $array = [
            new CameraPreset("minecraft:third_person", "", null, null, null, null, null, 0, false),
            new CameraPreset("minecraft:third_person_front", "", null, null, null, null, null, 0, false),
            new CameraPreset("minecraft:first_person", "", null, null, null, null, null, 0, false),
            new CameraPreset("minecraft:free", "", null, null, null, null, null, 0, false),
        ];
        if ($this->getPlayer()->isConnected()) {
            $this->getPlayer()->getNetworkSession()->sendDataPacket(CameraPresetsPacket::create($array));
        }
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function createTiming(callable $task, int $period = 20): void
    {
        $taskHandler = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($task): void {
            if ($this->getPlayer()->isConnected()) {
                $task($this, $this->seconds, $this->getPlayer());
                $this->seconds++;
            } else $this->stopTiming();
        }), $period);

        $this->setTaskHandler($taskHandler);
    }

    public function stopTiming(): bool
    {
        if (!is_null($this->getTaskHandler())) {
            if (!$this->getTaskHandler()->isCancelled()) {
                $this->getTaskHandler()->cancel();
                $this->setTaskHandler(null);
                $this->end();
            }
        }
        return !$this->hasTiming();
    }

    private function getTaskHandler(): ?TaskHandler
    {
        return $this->taskHandler;
    }

    private function setTaskHandler(?TaskHandler $taskHandler): void
    {
        $this->taskHandler = $taskHandler;
    }

    public function end(): void
    {
        if (!$this->hasTiming()) {
            $pk = CameraInstructionPacket::create(null, true, null);
            if ($this->getPlayer()->isConnected()) {
                $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
            }
        }
    }

    public function hasTiming(): bool
    {
        return !is_null($this->getTaskHandler());
    }

    public function addSecondsTiming(int $seconds): void
    {
        $this->seconds += $seconds;
    }

    public function removeSecondsTiming(int $seconds): void
    {
        $this->seconds -= $seconds;
    }

    public function setSecondsTiming(int $seconds): void
    {
        $this->seconds = $seconds;
    }

    public function setCameraPostionWithFacing(Position|Vector3 $position, int $easeType = EaseTypes::LINEAR, float $durationEase = 4.0, Position|Vector3|null $posCameraFixing = null): void
    {
        $cam = new CameraSetInstruction(
            3,
            new CameraSetInstructionEase(
                $easeType,
                $durationEase
            ),
            ($position instanceof Vector3 ? $position : $position->asVector3()),
            null,
            ($posCameraFixing instanceof Vector3 ? $posCameraFixing : $posCameraFixing->asVector3()),
            false
        );

        $pk = CameraInstructionPacket::create($cam, null, null);
        if ($this->getPlayer()->isConnected()) {
            $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        }

        $this->positionCamera = ($position instanceof Vector3 ? $position : $position->asVector3());
    }

    public function setCameraPositionWithRotation(Position|Vector3 $position, int $easeType = EaseTypes::LINEAR, float $durationEase = 4.0, float $pitch = 0, float $yaw = 0): void
    {
        $cam = new CameraSetInstruction(
            3,
            new CameraSetInstructionEase(
                $easeType,
                $durationEase
            ),
            ($position instanceof Vector3 ? $position : $position->asVector3()),
            new CameraSetInstructionRotation($pitch, $yaw),
            null,
            false
        );
        $pk = CameraInstructionPacket::create($cam, null, null);
        if ($this->getPlayer()->isConnected()) {
            $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        }

        $this->positionCamera = ($position instanceof Vector3 ? $position : $position->asVector3());
    }

    public function setCameraPosition(Position|Vector3 $position, int $easeType = EaseTypes::LINEAR, float $durationEase = 4.0, Position|Vector3|null $posCameraFixing = null, float $pitch = 0, float $yaw = 0): void
    {
        $cam = new CameraSetInstruction(
            3,
            new CameraSetInstructionEase(
                $easeType,
                $durationEase
            ),
            ($position instanceof Vector3 ? $position : $position->asVector3()),
            new CameraSetInstructionRotation($pitch, $yaw),
            ($posCameraFixing instanceof Vector3 ? $posCameraFixing : (is_null($posCameraFixing) ? null : $posCameraFixing->asVector3())),
            false
        );

        $pk = CameraInstructionPacket::create($cam, null, null);
        if ($this->getPlayer()->isConnected()) {
            $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        }

        $this->positionCamera = ($position instanceof Vector3 ? $position : $position->asVector3());
    }

    public function getPositionCamera(): Vector3
    {
        return $this->positionCamera;
    }

    public function sendFade(float $fadeStartTime, float $inFadeTime, float $quitFadeTime, Color|DyeColor $color): void
    {

        $hasColor = false;
        if ($color instanceof DyeColor) {
            if ($color->getDisplayName() === 'Black') {
                $r = 0.0;
                $g = 0.0;
                $b = 0.0;
                $hasColor = true;
            }
            $color = $color->getRgbValue();
        }

        if (!$hasColor) {
            $r = $color->getR() / 255 * 0.9 + 0.1;
            $g = $color->getG() / 255 * 0.9 + 0.1;
            $b = $color->getB() / 255 * 0.9 + 0.1;
        }

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

        if ($this->getPlayer()->isConnected()) {
            $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        }
    }

    public function addShakeCamera(float $intensity, float $duration, int $shakeType = ShakeTypes::TYPE_ROTATIONAL): void
    {
        $pk = CameraShakePacket::create($intensity, $duration, $shakeType, CameraShakePacket::ACTION_ADD);
        if ($this->getPlayer()->isConnected()) {
            $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        }
    }

    public function stopShakeCamera(): void
    {
        $pk = CameraShakePacket::create(0, 0, ShakeTypes::TYPE_ROTATIONAL, CameraShakePacket::ACTION_STOP);
        if ($this->getPlayer()->isConnected()) {
            $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        }
    }
}