<?php

namespace core\managers;

use core\Main;
use core\settings\traits\OwnedTrait;
use core\sql\Connexion;

class Manager
{
    use OwnedTrait;

    private Connexion $connexion;

    public function __construct(Main $plugin)
    {
        $this->setPlugin($plugin);
        $this->connexion = new Connexion();
    }

    public function getConnexion(): Connexion { return $this->connexion; }
}