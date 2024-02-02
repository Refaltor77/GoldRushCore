<?php

namespace core\entities\ia;

interface EntityAI
{

    public function chargeIdle(string $animateName): void;
    public function chargeRun(string $animateName): void;
    public function chargeWalk(string $animateName): void;
    public function chargeAttack(string $animateName, int $ticksDelays): void;
    public function chargeDeath(string $animateName): void;

    public function chargeCustom(): void;
}