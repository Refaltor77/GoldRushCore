<?php

namespace core\settings\jobs;

use pocketmine\block\BlockTypeIds;

interface Jobs
{

    const MINER_IDS = "miner";
    const FARMER_IDS = "farmer";
    const ASSASSIN_IDS = "assassin";
    const LUMBERJACK_IDS = "lumberJack";
    const HUNTER_IDS = "hunter";
    const COOK_IDS = "cook";


    # default jobs pattern
    const PATTERN = [
        self::MINER_IDS => [
            'xp' => 0,
            'lvl' => 0
        ],
        self::FARMER_IDS => [
            'xp' => 0,
            'lvl' => 0
        ],
        self::ASSASSIN_IDS => [
            'xp' => 0,
            'lvl' => 0
        ],
        self::LUMBERJACK_IDS => [
            'xp' => 0,
            'lvl' => 0
        ],
        self::HUNTER_IDS => [
            'xp' => 0,
            'lvl' => 0
        ],
        self::COOK_IDS => [
            'xp' => 0,
            'lvl' => 0
        ],
    ];

    # xp of all jobs
    const MINER_XP = [
        BlockTypeIds::COAL_ORE => 15,
        BlockTypeIds::IRON_ORE => 25,
        BlockTypeIds::REDSTONE_ORE => 10,
        BlockTypeIds::LAPIS_LAZULI_ORE => 25,
        BlockTypeIds::DIAMOND_ORE => 25,


        BlockTypeIds::DEEPSLATE_COAL_ORE => 15,
        BlockTypeIds::DEEPSLATE_IRON_ORE => 25,
        BlockTypeIds::DEEPSLATE_REDSTONE_ORE => 10,
        BlockTypeIds::DEEPSLATE_LAPIS_LAZULI_ORE => 25,
        BlockTypeIds::DEEPSLATE_DIAMOND_ORE => 25
    ];


    const FARMER_XP = [
        BlockTypeIds::WHEAT => 14,
        BlockTypeIds::BEETROOTS => 12,
        BlockTypeIds::CARROTS => 14,
        BlockTypeIds::POTATOES => 15,
        BlockTypeIds::MELON => 4,
        BlockTypeIds::PUMPKIN => 8,
        BlockTypeIds::CACTUS => 8,
        BlockTypeIds::SUGARCANE => 8
    ];

    const BUCHERON_XP = [
        BlockTypeIds::ACACIA_LOG => 18,
        BlockTypeIds::BIRCH_LOG => 14,
        BlockTypeIds::CHERRY_LOG => 16,
        BlockTypeIds::DARK_OAK_LOG => 10,
        BlockTypeIds::JUNGLE_LOG => 30,
        BlockTypeIds::MANGROVE_LOG => 24,
        BlockTypeIds::OAK_LOG => 16,
        BlockTypeIds::SPRUCE_LOG => 20,



        BlockTypeIds::ACACIA_WOOD => 18,
        BlockTypeIds::BIRCH_WOOD => 14,
        BlockTypeIds::CHERRY_WOOD => 16,
        BlockTypeIds::DARK_OAK_WOOD => 10,
        BlockTypeIds::JUNGLE_WOOD => 30,
        BlockTypeIds::MANGROVE_WOOD => 24,
        BlockTypeIds::OAK_WOOD => 16,
        BlockTypeIds::SPRUCE_WOOD => 20,
    ];

    const ASSASSIN_XP = [
        "kill" => 500,
        "assist" => 200,
    ];

    const LUMBERJACK_XP = [
        BlockTypeIds::ACACIA_WOOD . ":0" => 10,
    ];

    const HUNTER_XP = [

    ];

    const COOK_XP = [

    ];


    # lvl of all jobs
    const MINER_LVL = [
        1 => 1000,
        2 => 3000,
        3 => 5000,
        4 => 7000,
        5 => 9000,
        6 => 13000,
        7 => 17000,
        8 => 21000,
        10 => 25000,
        11 => 33000,
        12 => 41000,
        13 => 49000,
        14 => 57000,
        15 => 65000,
        16 => 81000,
        17 => 97000,
        18 => 113000,
        19 => 129000,
        20 => 145000,
        21 => 177000,
        22 => 209000,
        23 => 241000,
        24 => 273000,
        25 => 305000,
        26 => 369000,
        27 => 433000,
        28 => 497000,
        29 => 561000,
        30 => 625000,
    ];

    const FARMER_LVL = self::MINER_LVL;

    const LUMBERJACK_LVL = self::MINER_LVL;

    const ASSASSIN_LVL = [
        1 => 200,
        2 => 400,
        3 => 600,
        4 => 800,
        5 => 1000,
        6 => 1200,
        7 => 1400,
        8 => 1600,
        9 => 1800,
        10 => 2000,
        11 => 2200,
        12 => 2400,
        13 => 2600,
        14 => 2800,
        15 => 3000,
        16 => 3000,
        17 => 3000,
        18 => 3000,
        19 => 3000,
        20 => 3000,
        21 => 3500,
        22 => 3500,
        23 => 3500,
        24 => 3500,
        25 => 3500,
        26 => 4000,
        27 => 4000,
        28 => 4000,
        29 => 4000,
        30 => 4000
    ];

    const FARMER_REWARD = [
        5 => [
            "health" => 2,
            "fortune_farming" => 2,
        ],
        10 => [
            "health" => 3,
            "fortune_farming" => 3,
        ],
        15 => [
            "health" => 4,
            "fortune_farming" => 4
        ],
        20 => [
            "health" => 5,
            "fortune_farming" => 5,
        ]
    ];

}