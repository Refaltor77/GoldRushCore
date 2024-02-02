<?php

namespace core\traits;

use core\services\TpaService;

trait ServiceTrait
{
    public TpaService $tpa;

    public function getTpaService(): TpaService
    {
        return $this->tpa;
    }
}