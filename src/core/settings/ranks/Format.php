<?php

namespace core\settings\ranks;

use pocketmine\utils\TextFormat;

interface Format
{
    const FORMAT = [
        'PIONNIER' => '§7[Joueur] §e{faction} §7{pseudo}  §l§e»§r §7{msg}',
        'JOURNALISTE' => '§7[§5Journaliste§r§7] §e{faction} §7{pseudo} §l§e»§r §f{msg}',
        'YOUTUBE' => '§7[§cYout§fube§r§7] §e{faction} §7{pseudo} §l§e»§r §f{msg}',
        'BANDIT' => '§7[§0Bandit§r§7] §e{faction} §8{pseudo} §l§e»§r §f{msg}',
        'BRAQUEUR' => '§7[§0Braqueur§r§7] §7{faction} §f{pseudo} §l§0»§r §f{msg}',
        'COWBOY' => '§7[§6Cowboy§r§7] §7{faction} §f{pseudo} §l§e»§r §f{msg}',
        'MARSHALL' => '§7['.TextFormat::LIGHT_PURPLE.'Marshall§r§7] §7{faction} §f{pseudo} §l§d»§r §f{msg}',
        'SHERIF' => '§7[§6Shérif§r§7] §e{faction} §e{pseudo} §l§6»§r §f{msg}',
        'GUIDE' => '§7[§aGuide§7]§r §e{faction} §a{pseudo} §l§e»§r §a{msg}',
        'MODERATOR' => '§7[§3Modérateur§7]§r §b{pseudo} §l§3»§r §b{msg}',
        'MODO+' => '§7[§1Super Modérateur§7]§r §9{pseudo} §l§1»§r §9{msg}',
        'RESPONSABLE' => '§7[§2Responsable§7]§r §2{pseudo} §l§2»§r §2{msg}',
        'ADMIN' => '§7[§cAdministrateur§7]§r §4{pseudo} §l§4»§r §4{msg}',
    ];

    const FORMAT_DISCORD = [
        'PIONNIER' => '[Joueur] {faction} {pseudo} » {msg}',
        'JOURNALISTE' => '[Journaliste] {faction}{pseudo} » {msg}',
        'YOUTUBE' => '[Youtube] {faction} {pseudo} » {msg}',
        'BANDIT' => '[Bandit] {faction} {pseudo} » {msg}',
        'BRAQUEUR' => '[Braqueur] {faction} {pseudo} » {msg}',
        'COWBOY' => '[Cowboy] {faction} {pseudo} » {msg}',
        'MARSHALL' => '['.TextFormat::LIGHT_PURPLE.'Marshall] {faction} {pseudo} » {msg}',
        'SHERIF' => '[Shérif] {faction} {pseudo} » {msg}',
        'GUIDE' => '[Guide] {faction} {pseudo} » {msg}',
        'MODERATOR' => '[Modérateur] {pseudo} » {msg}',
        'MODO+' => '[Super Modérateur] {pseudo} » {msg}',
        'RESPONSABLE' => '[Responsable] {pseudo} » {msg}',
        'ADMIN' => '[Administrateur] {pseudo} » {msg}',
    ];

    const CONVERSION_WRITE = [
        'joueur' => 'PLAYER',
        'youtubeur' => 'YOUTUBE',
        'journaliste' => 'JOURNALISTE',
        'bandit' => 'BANDIT',
        'braqueur' => 'BRAQUEUR',
        'cowboy' => 'COWBOY',
        'marshall' => 'MARSHALL',
        'sherif' => 'SHERIF',
        'guide' => 'GUIDE',
        'modo' => 'MODO',
        'modo+' => 'MODO+',
        'responsable' => 'RESPONSABLE',
        'admin' => 'ADMIN',
    ];

    const CONVERSION_RANK = [
        'PLAYER' => 'Joueur',
        'JOURNALISTE' => 'Journaliste',
        'YOUTUBE' => 'Youtubeur',
        'BANDIT' => 'Bandit',
        'BRANQUEUR' => 'Branqueur',
        'COWBOY' => 'Cowboy',
        'MARSHALL' => 'Marshall',
        'SHERIF' => 'Shérif',
        'GUIDE' => 'Guide',
        'MODO' => 'Modérateur',
        'MODO+' => 'Super Modérateur',
        'RESPONSABLE' => 'Responsable',
        'ADMIN' => 'Administrateur'
    ];
}