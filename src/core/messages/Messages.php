<?php

namespace core\messages;

class Messages
{
    const PREFIX = "§l§6»§r ";
    const FACTION_CREATE_FACTION_SUCCESS = "§aVotre faction a été crée avec succès !";
    const FACTION_CREATE_FACTION_REFUSE_HAVE_FACTION = "§cVous êtes déjà dans une faction !";
    const FACTION_CREATE_FACTION_REFUSE_NAME_LONG = "§cVotre nom de faction ne doit pas dépasser §410 §clettres !";
    const FACTION_CREATE_FACTION_REFUSE_NAME_SMALL = "§cVotre nom de faction doit contenir au moins §43 §clettres !";
    const FACTION_CREATE_FACTION_REFUSE_NAME_EXIST = "§cLe nom de votre faction est déjà enregistré !";


    public static function message(string $msg): string
    {
        return Prefix::PREFIX_GOOD . $msg;
    }
}