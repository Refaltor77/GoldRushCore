<?php

namespace core\managers\shop;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Dropdown;
use core\api\form\elements\Image;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\api\form\elements\Toggle;
use core\api\form\MenuForm;
use core\Main;
use core\managers\Manager;
use core\messages\Messages;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class ShopManager extends Manager
{

    use SoundTrait;

    public array $shop = [
        'Farming' => [
            'category_name' => 'Farming',
            'image' => 'textures/items/seeds_wheat',
            'items' => [
                [
                    'name' => 'Blé',
                    'image' => 'textures/items/wheat',
                    'idMeta' => 'minecraft:wheat',
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Graine de blé',
                    'image' => 'textures/items/seeds_wheat',
                    'idMeta' => 'minecraft:seeds',
                    'buy' => 60,
                    'sell' => 2,
                    'quantity' => 1
                ],
                [
                    'name' => "Graine d'obsidienne'",
                    'image' => 'textures/items/seeds_wheat_obsidian',
                    'idMeta' => Ids::SEEDS_WHEAT_OBSIDIAN,
                    'buy' => 400,
                    'sell' => 3,
                    'quantity' => 1
                ],
                [
                    'name' => 'Graine de melon',
                    'image' => 'textures/items/seeds_melon',
                    'idMeta' => 'minecraft:melon_seeds',
                    'buy' => 100,
                    'quantity' => 1
                ],
                [
                    'name' => 'Melon',
                    'image' => 'textures/items/melon',
                    'idMeta' => 'minecraft:melon_slice',
                    'sell' => 2,
                    'quantity' => 1
                ],
                [
                    'name' => 'Carottes',
                    'image' => 'textures/items/carrot',
                    'idMeta' => 'minecraft:carrots',
                    'buy' => 100,
                    'sell' => 1,
                    'quantity' => 1
                ],
                [
                    'name' => 'Patate',
                    'image' => 'textures/items/potato',
                    'idMeta' => 'minecraft:potatoes',
                    'buy' => 100,
                    'sell' => 2,
                    'quantity' => 1
                ],
                [
                    'name' => 'Graine de betterave',
                    'image' => 'textures/items/seeds_beetroot',
                    'idMeta' => 'minecraft:beetroot_seeds',
                    'buy' => 100,
                    'quantity' => 1
                ],
                [
                    'name' => 'Betterave',
                    'image' => 'textures/items/beetroot',
                    'idMeta' => 'minecraft:beetroot',
                    'buy' => 100,
                    'quantity' => 1
                ],
                [
                    'name' => 'Graine de citrouille',
                    'image' => 'textures/items/seeds_pumpkin',
                    'idMeta' => 'minecraft:pumpkin_seeds',
                    'buy' => 100,
                    'quantity' => 1
                ],
                [
                    'name' => 'Citrouille',
                    'image' => 'textures/blocks/pumpkin_face_off',
                    'idMeta' => 'minecraft:pumpkin',
                    'sell' => 4,
                    'quantity' => 1
                ],
                [
                    'name' => 'Canne à sucre',
                    'image' => 'textures/items/reeds',
                    'idMeta' => 'minecraft:sugarcane',
                    'buy' => 100,
                    'sell' => 4,
                    'quantity' => 1
                ],
                [
                    'name' => 'Cactus',
                    'image' => 'textures/blocks/cactus_side',
                    'idMeta' => 'minecraft:cactus',
                    'buy' => 100,
                    'sell' => 4,
                    'quantity' => 1
                ],
                [
                    'name' => 'Raisin',
                    'image' => 'textures/items/goldrush_grape',
                    'idMeta' => Ids::RAISIN,
                    'buy' => 500,
                    'sell' => 4,
                    'quantity' => 1
                ],
                [
                    'name' => 'Baie bleue',
                    'image' => 'textures/items/sweet_berries_blue',
                    'idMeta' => Ids::BERRY_BLUE,
                    'buy' => 500,
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Baie rose',
                    'image' => 'textures/items/berry_pink',
                    'idMeta' => Ids::BERRY_PINK,
                    'buy' => 500,
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Baie jaune',
                    'image' => 'textures/items/berry_yellow',
                    'idMeta' => Ids::BERRY_YELLOW,
                    'buy' => 500,
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Baie noir',
                    'image' => 'textures/items/berry_black',
                    'idMeta' => Ids::BERRY_BLACK,
                    'buy' => 500,
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Pousse de sapin',
                    'image' => 'textures/blocks/sapling_spruce',
                    'idMeta' => "minecraft:spruce_sapling",
                    'buy' => 20,
                    'quantity' => 1
                ],
                [
                    'name' => 'Pousse de chêne',
                    'image' => 'textures/blocks/sapling_oak',
                    'idMeta' => "minecraft:sapling",
                    'buy' => 20,
                    'quantity' => 1
                ],
                [
                    'name' => 'Pousse de bouleau',
                    'image' => 'textures/blocks/sapling_birch',
                    'idMeta' => "minecraft:birch_sapling",
                    'buy' => 20,
                    'quantity' => 1
                ],
                [
                    'name' => 'Pousse de chêne noir',
                    'image' => 'textures/blocks/sapling_roofed_oak',
                    'idMeta' => "minecraft:dark_oak_sapling",
                    'buy' => 20,
                    'quantity' => 1
                ],
                [
                    'name' => "Pousse d'acacia",
                    'image' => 'textures/blocks/sapling_acacia',
                    'idMeta' => "minecraft:acacia_sapling",
                    'buy' => 20,
                    'quantity' => 1
                ],
                [
                    'name' => 'Pousse de bois tropical',
                    'image' => 'textures/blocks/sapling_jungle',
                    'idMeta' => "minecraft:jungle_sapling",
                    'buy' => 20,
                    'quantity' => 1
                ],
            ]
        ],
        'Minage' => [
            'category_name' => 'Minage',
            'image' => 'textures/items/diamond',
            'items' =>
                [
                    [
                        'name' => "Platine",
                        'image' => 'textures/items/platinum_ingot',
                        'idMeta' => Ids::PLATINUM_INGOT,
                        'sell' => 220,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Améthyste",
                        'image' => 'textures/items/amethyst_ingot',
                        'idMeta' => Ids::AMETHYST_INGOT,
                        'sell' => 130,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Émeraude",
                        'image' => 'textures/items/emerald_ingot',
                        'idMeta' => Ids::EMERALD_INGOT,
                        'sell' => 90,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Diamant',
                        'image' => 'textures/items/diamond',
                        'idMeta' => 'minecraft:diamond',
                        'sell' => 48,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Soufre',
                        'image' => 'textures/items/sulfur',
                        'idMeta' => Ids::SULFUR_POWDER,
                        'sell' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Lingot de cuivre",
                        'image' => 'textures/items/copper_ingot',
                        'idMeta' => Ids::COPPER_INGOT,
                        'sell' => 32,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Charbon',
                        'image' => 'textures/items/coal',
                        'idMeta' => 'minecraft:coal',
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Lingot de fer',
                        'image' => 'textures/items/iron_ingot',
                        'idMeta' => 'minecraft:iron_ingot',
                        'sell' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Redstone',
                        'image' => 'textures/items/redstone_dust',
                        'idMeta' => 'minecraft:redstone_dust',
                        'sell' => 6,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Lapis lazuli',
                        'image' => 'textures/items/dye_powder_blue',
                        'idMeta' => 'minecraft:lapis_lazuli',
                        'sell' => 20,
                        'quantity' => 1
                    ],
                ]
        ],
        'Food' => [
            'category_name' => 'Food',
            'image' => 'textures/items/beef_cooked',
            'items' =>
                [
                    [
                        'name' => 'Boeuf cuit',
                        'image' => 'textures/items/beef_cooked',
                        'idMeta' => 'minecraft:cooked_beef',
                        'buy' => 32,
                        'sell' => 24,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Poulet cuit',
                        'image' => 'textures/items/chicken_cooked',
                        'idMeta' => 'minecraft:cooked_chicken',
                        'buy' => 32,
                        'sell' => 24,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Mouton cuit',
                        'image' => 'textures/items/mutton_cooked',
                        'idMeta' => 'minecraft:cooked_mutton',
                        'buy' => 32,
                        'sell' => 24,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Porc cuit',
                        'image' => 'textures/items/porkchop_cooked',
                        'idMeta' => 'minecraft:cooked_porkchop',
                        'buy' => 32,
                        'sell' => 24,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Boeuf cru',
                        'image' => 'textures/items/beef_raw',
                        'idMeta' => 'minecraft:raw_beef',
                        'buy' => 24,
                        'sell' => 6,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Poulet cru',
                        'image' => 'textures/items/chicken_raw',
                        'idMeta' => 'minecraft:raw_chicken',
                        'buy' => 24,
                        'sell' => 6,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Mouton cru',
                        'image' => 'textures/items/mutton_raw',
                        'idMeta' => 'minecraft:raw_mutton',
                        'buy' => 24,
                        'sell' => 6,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Porc cru',
                        'image' => 'textures/items/porkchop_raw',
                        'idMeta' => 'minecraft:raw_porkchop',
                        'buy' => 24,
                        'sell' => 6,
                        'quantity' => 1
                    ]
                ]
        ],
        'Utilitaires' => [
            'category_name' => 'Utilitaires',
            'image' => 'textures/blocks/chest_front',
            'items' =>
                [
                    [
                        'name' => 'Coffre',
                        'image' => 'textures/blocks/chest_front',
                        'idMeta' => 'minecraft:chest',
                        'buy' => 200,
                        'quantity' => 1
                    ],

                    [
                        'name' => 'Enclume',
                        'image' => 'textures/blocks/anvil_base',
                        'idMeta' => 'minecraft:anvil',
                        'buy' => 500,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Scie à pierre',
                        'image' => 'textures/blocks/stonecutter2_side',
                        'idMeta' => 'minecraft:stonecutter',
                        'buy' => 250,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Table de craft',
                        'image' => 'textures/blocks/crafting_table_front',
                        'idMeta' => 'minecraft:crafting_table',
                        'buy' => 100,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Table d'enchantement",
                        'image' => 'textures/blocks/enchanting_table_side',
                        'idMeta' => 'minecraft:enchanting_table',
                        'buy' => 4000,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'EnderChest',
                        'image' => 'textures/blocks/ender_chest_side',
                        'idMeta' => 'minecraft:ender_chest',
                        'buy' => 7000,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Seau",
                        'image' => 'textures/items/bucket_empty',
                        'idMeta' => 'minecraft:bucket',
                        'buy' => 100,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Seau d'eau",
                        'image' => 'textures/items/bucket_water',
                        'idMeta' => 'minecraft:water_bucket',
                        'buy' => 150,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Seau de lave",
                        'image' => 'textures/items/bucket_lava',
                        'idMeta' => 'minecraft:lava_bucket',
                        'buy' => 150,
                        'quantity' => 1
                    ],
                ]
        ],
        'Loots' => [
            'category_name' => 'Loots',
            'image' => 'textures/items/bone',
            'items' =>
                [
                    [
                        'name' => 'Spawner',
                        'image' => 'textures/blocks/mob_spawner',
                        'idMeta' => 'minecraft:mob_spawner',
                        'buy' => 2000000,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Oeuf de vache',
                        'image' => 'textures/items/egg_cow',
                        'idMeta' => Ids::EGG_COW,
                        'buy' => 1000000,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Oeuf de mouton',
                        'image' => 'textures/items/egg_sheep',
                        'idMeta' => Ids::EGG_MUTTON,
                        'buy' => 1000000,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Oeuf d'enderman",
                        'image' => 'textures/items/egg_enderman',
                        'idMeta' => Ids::EGG_ENDERMAN,
                        'buy' => 2000000,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Oeuf de poulet',
                        'image' => 'textures/items/egg_chicken',
                        'idMeta' => Ids::EGG_CHICKEN,
                        'buy' => 1000000,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Oeuf de squelette',
                        'image' => 'textures/items/egg_skeleton',
                        'idMeta' => Ids::EGG_SKELETON,
                        'buy' => 2000000,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Oeuf de creeper',
                        'image' => 'textures/items/egg_creeper',
                        'idMeta' => Ids::EGG_CREEPER,
                        'buy' => 2000000,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Oeuf de zombie',
                        'image' => 'textures/items/egg_zombie',
                        'idMeta' => Ids::EGG_ZOMBIE,
                        'buy' => 1000000,
                        'quantity' => 1
                    ],
                    // TODO: add spider egg entiti
                    /*
                    [
                        'name' => 'Oeuf de poulet',
                        'image' => 'textures/items/egg_chicken',
                        'idMeta' => Ids::EGG_CHICKEN,
                        'buy' => 1000000,
                        'quantity' => 1
                    ],
                    */
                    [
                        'name' => 'Cuir',
                        'image' => 'textures/items/leather',
                        'idMeta' => 'minecraft:leather',
                        'buy' => 60,
                        'sell' => 35,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Plume',
                        'image' => 'textures/items/feather',
                        'idMeta' => 'minecraft:feather',
                        'buy' => 60,
                        'sell' => 35,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Poudre à canon',
                        'image' => 'textures/items/gunpowder',
                        'idMeta' => 'minecraft:gunpowder',
                        'buy' => 450,
                        'sell' => 50,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Chair putréfiée',
                        'image' => 'textures/items/rotten_flesh',
                        'idMeta' => 'minecraft:rotten_flesh',
                        'buy' => 80,
                        'sell' => 50,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Os',
                        'image' => 'textures/items/bone',
                        'idMeta' => 'minecraft:bone',
                        'buy' => 100,
                        'sell' => 50,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Flèche',
                        'image' => 'textures/items/arrow',
                        'idMeta' => 'minecraft:arrow',
                        'buy' => 80,
                        'sell' => 50,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Oeil de l'ender",
                        'image' => 'textures/items/ender_pearl',
                        'idMeta' => Ids::ENDER_PEARL,
                        'sell' => 100,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Fil',
                        'image' => 'textures/items/string',
                        'idMeta' => 'minecraft:string',
                        'buy' => 80,
                        'sell' => 50,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Oeil d'araignée",
                        'image' => 'textures/items/spider_eye',
                        'idMeta' => 'minecraft:fermented_spider_eye',
                        'buy' => 80,
                        'sell' => 50,
                        'quantity' => 1
                    ],
                ]
        ],
        'Blocs' => [
            'category_name' => 'Blocs',
            'image' => 'textures/blocks/cobblestone',
            'items' =>
                [
                    [
                        'name' => 'Bûche de chêne',
                        'image' => 'textures/blocks/log_oak',
                        'idMeta' => 'minecraft:oak_log',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bûche de sapin',
                        'image' => 'textures/blocks/log_spruce',
                        'idMeta' => 'minecraft:spruce_log',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bûche de bouleau',
                        'image' => 'textures/blocks/log_birch',
                        'idMeta' => 'minecraft:birch_log',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bûche de bois tropical',
                        'image' => 'textures/blocks/log_jungle',
                        'idMeta' => 'minecraft:jungle_log',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bûche de chêne noir',
                        'image' => 'textures/blocks/log_big_oak',
                        'idMeta' => 'minecraft:dark_oak_log',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bûche d'acacia",
                        'image' => 'textures/blocks/log_acacia',
                        'idMeta' => 'minecraft:acacia_log',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bois de chêne',
                        'image' => 'textures/blocks/log_oak',
                        'idMeta' => 'minecraft:oak_wood',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bois de sapin',
                        'image' => 'textures/blocks/log_spruce',
                        'idMeta' => 'minecraft:spruce_wood',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bois de bouleau',
                        'image' => 'textures/blocks/log_birch',
                        'idMeta' => 'minecraft:birch_wood',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bois tropical',
                        'image' => 'textures/blocks/log_jungle',
                        'idMeta' => 'minecraft:jungle_wood',
                        'sell' => 12,
                        'buy' => 50,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bois de chêne noir',
                        'image' => 'textures/blocks/log_big_oak',
                        'idMeta' => 'minecraft:dark_oak_wood',
                        'buy' => 50,
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bois d'acacia",
                        'image' => 'textures/blocks/log_acacia',
                        'buy' => 50,
                        'idMeta' => 'minecraft:acacia_wood',
                        'sell' => 12,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Cobblestone",
                        'image' => 'textures/blocks/cobblestone',
                        'idMeta' => 'minecraft:cobblestone',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Terre",
                        'image' => 'textures/blocks/dirt',
                        'idMeta' => 'minecraft:dirt',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Herbe",
                        'image' => 'textures/blocks/grass_top',
                        'idMeta' => 'minecraft:grass',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Verre",
                        'image' => 'textures/blocks/glass',
                        'idMeta' => 'minecraft:glass',
                        'buy' => 40,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Pierre",
                        'image' => 'textures/blocks/stone',
                        'idMeta' => 'minecraft:stone',
                        'buy' => 40,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Gravier",
                        'image' => 'textures/blocks/gravel',
                        'idMeta' => 'minecraft:gravel',
                        'buy' => 20,
                        'sell' => 3,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Sable",
                        'image' => 'textures/blocks/sand',
                        'idMeta' => 'minecraft:sand',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Sable rouge",
                        'image' => 'textures/blocks/red_sand',
                        'idMeta' => 'minecraft:red_sand',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Granite",
                        'image' => 'textures/blocks/stone_granite',
                        'idMeta' => 'minecraft:granite',
                        'buy' => 30,
                        'sell' => 3,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Andesite",
                        'image' => 'textures/blocks/stone_andesite',
                        'idMeta' => 'minecraft:andesite',
                        'buy' => 30,
                        'sell' => 3,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Diorite",
                        'image' => 'textures/blocks/stone_diorite',
                        'idMeta' => 'minecraft:diorite',
                        'buy' => 30,
                        'sell' => 3,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Netherack",
                        'image' => 'textures/blocks/netherrack',
                        'idMeta' => 'minecraft:netherack',
                        'buy' => 25,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Soulsand",
                        'image' => 'textures/blocks/soul_sand',
                        'idMeta' => 'minecraft:soul_sand',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Basalt",
                        'image' => 'textures/blocks/basalt_side',
                        'idMeta' => 'minecraft:basalt',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de magma",
                        'image' => 'textures/blocks/magma',
                        'idMeta' => 'minecraft:magma',
                        'buy' => 50,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Tuf",
                        'image' => 'textures/blocks/tuff',
                        'idMeta' => 'minecraft:tuff',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Calcite",
                        'image' => 'textures/blocks/calcite',
                        'idMeta' => 'minecraft:calcite',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Glace",
                        'image' => 'textures/blocks/ice',
                        'idMeta' => 'minecraft:ice',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Glace compacte",
                        'image' => 'textures/blocks/ice_packed',
                        'idMeta' => 'minecraft:packed_ice',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Glace bleu",
                        'image' => 'textures/blocks/blue_ice',
                        'idMeta' => 'minecraft:blue_ice',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de neige",
                        'image' => 'textures/blocks/snow',
                        'idMeta' => 'minecraft:snow',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Glowstone",
                        'image' => 'textures/blocks/glowstone',
                        'idMeta' => 'minecraft:glowstone',
                        'buy' => 45,
                        'quantity' => 1
                    ],

                    [
                        'name' => "Lampe aquatique",
                        'image' => 'textures/blocks/sea_lantern',
                        'idMeta' => 'minecraft:sea_lantern',
                        'buy' => 45,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Champilampe",
                        'image' => 'textures/blocks/shroomlight',
                        'idMeta' => 'minecraft:shroomlight',
                        'buy' => 45,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Sculk",
                        'image' => 'textures/blocks/sculk',
                        'idMeta' => 'minecraft:sculk',
                        'buy' => 30,
                        'quantity' => 1
                    ],

                    [
                        'name' => "Bibliothèque",
                        'image' => 'textures/blocks/bookshelf',
                        'idMeta' => 'minecraft:bookshelf',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de musique",
                        'image' => 'textures/blocks/jukebox_side',
                        'idMeta' => 'minecraft:jukebox',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de brique",
                        'image' => 'textures/blocks/brick',
                        'idMeta' => 'minecraft:bricks',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de quartz",
                        'image' => 'textures/blocks/quartz_block_bottom',
                        'idMeta' => 'minecraft:quartz_block',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de brique du nether",
                        'image' => 'textures/blocks/cracked_nether_bricks',
                        'idMeta' => 'minecraft:nether_brick',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de brique du nether rouge",
                        'image' => 'textures/blocks/red_nether_brick',
                        'idMeta' => 'minecraft:red_nether_brick',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Pierre taillé",
                        'image' => 'textures/blocks/stonebrick',
                        'idMeta' => 'minecraft:stonebrick',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Brique de boue",
                        'image' => 'textures/blocks/mud_bricks',
                        'idMeta' => 'minecraft:mud_bricks',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Brique de purpur",
                        'image' => 'textures/blocks/purpur_block',
                        'idMeta' => 'minecraft:purpur',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Brique de prismarine",
                        'image' => 'textures/blocks/prismarine_bricks',
                        'idMeta' => 'minecraft:prismarine_bricks',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Prismarine",
                        'image' => 'textures/blocks/prismarine_rough',
                        'idMeta' => 'minecraft:prismarine',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Prismarine sombre",
                        'image' => 'textures/blocks/prismarine_dark',
                        'idMeta' => 'minecraft:dark_prismarine',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Blackstone taillé",
                        'image' => 'textures/blocks/polished_blackstone',
                        'idMeta' => 'minecraft:polished_blackstone',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Poudre de béton blanche",
                        'image' => 'textures/blocks/concrete_powder_white',
                        'idMeta' => 'minecraft:concrete_powder',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Laine blanche",
                        'image' => 'textures/blocks/wool_colored_white',
                        'idMeta' => 'minecraft:wool',
                        'buy' => 30,
                        'sell' => 10,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Terracotta",
                        'image' => 'textures/blocks/hardened_clay',
                        'idMeta' => 'minecraft:hardened_clay',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de rayon de miel",
                        'image' => 'textures/blocks/honeycomb',
                        'idMeta' => 'minecraft:honeycomb_block',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Grès",
                        'image' => 'textures/blocks/clay',
                        'idMeta' => 'minecraft:clay',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Bloc de slime",
                        'image' => 'textures/blocks/slime',
                        'idMeta' => 'minecraft:slime_block',
                        'buy' => 45,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille de chêne",
                        'image' => 'textures/blocks/leaves_oak',
                        'idMeta' => 'minecraft:oak_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille de sapin",
                        'image' => 'textures/blocks/leaves_spruce',
                        'idMeta' => 'minecraft:spruce_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille de bouleau",
                        'image' => 'textures/blocks/leaves_birch',
                        'idMeta' => 'minecraft:birch_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille de tropical",
                        'image' => 'textures/blocks/leaves_jungle',
                        'idMeta' => 'minecraft:jungle_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille de chêne noir",
                        'image' => 'textures/blocks/leaves_big_oak',
                        'idMeta' => 'minecraft:dark_oak_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille d'acacia",
                        'image' => 'textures/blocks/leaves_acacia',
                        'idMeta' => 'minecraft:acacia_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille de cerisier",
                        'image' => 'textures/blocks/cherry_leaves',
                        'idMeta' => 'minecraft:cherry_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille d'azalée",
                        'image' => 'textures/blocks/azalea_leaves',
                        'idMeta' => 'minecraft:azalea_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Feuille d'azalée en fleur",
                        'image' => 'textures/blocks/azalea_leaves_flowers',
                        'idMeta' => 'minecraft:flowering_azalea_leaves',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                ]
        ],
        'Deco' => [
            'category_name' => 'Decoration',
            'image' => 'textures/blocks/dirt',
            'items' =>
                [
                    [
                        'name' => 'Colorant noir',
                        'image' => 'textures/items/dye_powder_black',
                        'idMeta' => 'minecraft:black_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant bleu',
                        'image' => 'textures/items/dye_powder_blue',
                        'idMeta' => 'minecraft:blue_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant marron',
                        'image' => 'textures/items/dye_powder_brown',
                        'idMeta' => 'minecraft:brown_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant bleu cyan',
                        'image' => 'textures/items/dye_powder_cyan',
                        'idMeta' => 'minecraft:cyan_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant gris',
                        'image' => 'textures/items/dye_powder_gray',
                        'idMeta' => 'minecraft:gray_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant vert',
                        'image' => 'textures/items/dye_powder_green',
                        'idMeta' => 'minecraft:green_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant bleu clair',
                        'image' => 'textures/items/dye_powder_light_blue',
                        'idMeta' => 'minecraft:light_blue_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant gris clair',
                        'image' => 'textures/items/dye_powder_silver',
                        'idMeta' => 'minecraft:light_gray_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant vert clair',
                        'image' => 'textures/items/dye_powder_lime',
                        'idMeta' => 'minecraft:lime_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant magenta',
                        'image' => 'textures/items/dye_powder_magenta',
                        'idMeta' => 'minecraft:magenta_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant orange',
                        'image' => 'textures/items/dye_powder_orange',
                        'idMeta' => 'minecraft:orange_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant rose',
                        'image' => 'textures/items/dye_powder_pink',
                        'idMeta' => 'minecraft:pink_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant violet',
                        'image' => 'textures/items/dye_powder_purple',
                        'idMeta' => 'minecraft:purple_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant rouge',
                        'image' => 'textures/items/dye_powder_red',
                        'idMeta' => 'minecraft:red_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant blanc',
                        'image' => 'textures/items/dye_powder_white',
                        'idMeta' => 'minecraft:white_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Colorant jaune',
                        'image' => 'textures/items/dye_powder_yellow',
                        'idMeta' => 'minecraft:yellow_dye',
                        'buy' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Lierre',
                        'image' => 'textures/blocks/vine',
                        'idMeta' => 'minecraft:vine',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Nénuphar',
                        'image' => 'textures/blocks/carried_waterlily',
                        'idMeta' => 'minecraft:water_lily',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Arbuste mort',
                        'image' => 'textures/blocks/deadbush',
                        'idMeta' => 'minecraft:deadbush',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Bougie',
                        'image' => 'textures/blocks/candles/white_candle',
                        'idMeta' => 'minecraft:candle',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Lanterne',
                        'image' => 'textures/blocks/lantern',
                        'idMeta' => 'minecraft:lantern',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Lanterne des âmes',
                        'image' => 'textures/blocks/soul_lantern',
                        'idMeta' => 'minecraft:soul_lantern',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Cadre',
                        'image' => 'textures/items/item_frame',
                        'idMeta' => 'minecraft:frame',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Pot de fleur',
                        'image' => 'textures/blocks/flower_pot',
                        'idMeta' => 'minecraft:flower_pot',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Barre de l'end",
                        'image' => 'textures/blocks/end_rod',
                        'idMeta' => 'minecraft:end_rod',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Cloche",
                        'image' => 'textures/blocks/bell_side',
                        'idMeta' => 'minecraft:bell',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Chaîne",
                        'image' => 'textures/blocks/chain1',
                        'idMeta' => 'minecraft:chain',
                        'buy' => 30,
                        'quantity' => 1
                    ],
                ]
        ]
    ];


    public array $sell = [
        'Farming' => [
            'category_name' => 'Farming',
            'image' => 'textures/items/wheat',
            'items' => [
                [
                    'name' => 'Blé',
                    'image' => 'textures/items/wheat',
                    'idMeta' => 'minecraft:wheat',
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Melon',
                    'image' => 'textures/items/melon',
                    'idMeta' => 'minecraft:melon_slice',
                    'sell' => 2,
                    'quantity' => 1
                ],
                [
                    'name' => 'Carottes',
                    'image' => 'textures/items/carrot',
                    'idMeta' => 'minecraft:carrot',
                    'sell' => 1,
                    'quantity' => 1
                ],
                [
                    'name' => 'Betterave',
                    'image' => 'textures/items/beetroot',
                    'idMeta' => 'minecraft:beetroot',
                    'sell' => 1,
                    'quantity' => 1
                ],
                [
                    'name' => 'Citrouille',
                    'image' => 'textures/blocks/pumpkin_face_off',
                    'idMeta' => 'minecraft:pumpkin',
                    'sell' => 4,
                    'quantity' => 1
                ],
                [
                    'name' => 'Canne à sucre',
                    'image' => 'textures/items/sugarcane',
                    'idMeta' => 'minecraft:sugarcane',
                    'sell' => 4,
                    'quantity' => 1
                ],
                [
                    'name' => 'Cactus',
                    'image' => 'textures/blocks/cactus',
                    'idMeta' => 'minecraft:cactus',
                    'sell' => 4,
                    'quantity' => 1
                ],
                [
                    'name' => 'Raisin',
                    'image' => 'textures/items/goldrush_grape',
                    'idMeta' => Ids::RAISIN,
                    'sell' => 4,
                    'quantity' => 1
                ],
                [
                    'name' => 'Baie bleue',
                    'image' => 'textures/items/sweet_berries_blue',
                    'idMeta' => Ids::BERRY_BLUE,
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Baie rose',
                    'image' => 'textures/items/berry_pink',
                    'idMeta' => Ids::BERRY_PINK,
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Baie jaune',
                    'image' => 'textures/items/berry_yellow',
                    'idMeta' => Ids::BERRY_YELLOW,
                    'sell' => 6,
                    'quantity' => 1
                ],
                [
                    'name' => 'Baie noir',
                    'image' => 'textures/items/berry_black',
                    'idMeta' => Ids::BERRY_BLACK,
                    'sell' => 6,
                    'quantity' => 1
                ],
            ]
        ],
        'Minage' => [
            'category_name' => 'Minage',
            'image' => 'textures/items/diamond',
            'items' =>
                [
                    [
                        'name' => "Poudre d'or",
                        'image' => 'textures/items/gold_powder',
                        'idMeta' => Ids::GOLD_POWDER,
                        'sell' => 500,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Platine",
                        'image' => 'textures/items/platinum_ingot',
                        'idMeta' => Ids::PLATINUM_INGOT,
                        'sell' => 150,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Améthyste",
                        'image' => 'textures/items/amethyst_ingot',
                        'idMeta' => Ids::AMETHYST_INGOT,
                        'sell' => 80,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Émeraude",
                        'image' => 'textures/items/emerald_ingot',
                        'idMeta' => Ids::EMERALD_INGOT,
                        'sell' => 60,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Diamant',
                        'image' => 'textures/items/diamond',
                        'idMeta' => 'minecraft:diamond',
                        'sell' => 50,
                        'quantity' => 1
                    ],
                    [
                        'name' => "Lingot de cuivre",
                        'image' => 'textures/items/copper_ingot',
                        'idMeta' => Ids::COPPER_INGOT,
                        'sell' => 25,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Charbon',
                        'image' => 'textures/items/coal',
                        'idMeta' => 'minecraft:coal',
                        'sell' => 5,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Lingot de fer',
                        'image' => 'textures/items/iron_ingot',
                        'idMeta' => 'minecraft:iron_ingot',
                        'sell' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Redstone',
                        'image' => 'textures/items/redstone_dust',
                        'idMeta' => 'minecraft:redstone_dust',
                        'sell' => 2,
                        'quantity' => 1
                    ],
                ]
        ],
        'Food' => [
            'category_name' => 'Food',
            'image' => 'textures/items/apple',
            'items' =>
                [
                    [
                        'name' => 'Pomme',
                        'image' => 'textures/items/apple',
                        'idMeta' => 'minecraft:apple',
                        'sell' => 25,
                        'quantity' => 1
                    ],
                ]
        ],
        'Loots' => [
            'category_name' => 'Loots',
            'image' => 'textures/items/bone',
            'items' =>
                [
                    [
                        'name' => 'Cuir',
                        'image' => 'textures/items/leather',
                        'idMeta' => 'minecraft:leather',
                        'sell' => 10,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Plume',
                        'image' => 'textures/items/feather',
                        'idMeta' => 'minecraft:feather',
                        'sell' => 10,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Poudre à canon',
                        'image' => 'textures/items/gunpowder',
                        'idMeta' => 'minecraft:gunpowder',
                        'sell' => 10,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Chair putrifiée',
                        'image' => 'textures/items/rotten_flesh',
                        'idMeta' => 'minecraft:rotten_flesh',
                        'sell' => 20,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Os',
                        'image' => 'textures/items/bone',
                        'idMeta' => 'minecraft:bone',
                        'sell' => 10,
                        'quantity' => 1
                    ],
                ]
        ]
    ];


    private array $fastCache = [];


    public function __construct(Main $plugin)
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);

        if ($config->exists('gen')) {

        } else {
            $config->setAll([
                'gen' => true,
                'sell' => $this->sell,
                'shop' => $this->shop
            ]);
            $config->save();
        }

        parent::__construct($plugin);
    }



    public function mainPage(Player $player, string $sendType = "")
    {
        $shops = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $shops->getAll()['shop'];
        $sell = $shops->getAll()['sell'];


        if (!empty($sendType)) {
            switch ($sendType) {
                case 'shop':
                    $arrayBtn = [];
                    foreach ($shop as $category => $name) {
                        if (isset($name['image'])) {
                            $arrayBtn[] = new Button($name['category_name'], new Image($name['image']));
                        } else {
                            $arrayBtn[] = new Button($name['category_name']);
                        }
                    }
                    $arrayBtn[] = new Button("§cRetour");
                    $form = new MenuForm('§eSHOP', '', $arrayBtn, function (Player $player, Button $button): void {
                        $target = $button->getText();
                        if (is_null($target)) return;
                        if ($target === '§cRetour') {
                            $this->mainPage($player);
                        } else $this->shop($player, $target);
                    });
                    $player->sendForm($form);
                    break;
                case 'sell':
                    $arrayBtn = [];
                    foreach ($sell as $category => $name) {
                        if (isset($name['image'])) {
                            $arrayBtn[] = new Button($name['category_name'], new Image($name['image']));
                        } else {
                            $arrayBtn[] = new Button($name['category_name']);
                        }
                    }
                    $arrayBtn[] = new Button("§cRetour");
                    $form = new MenuForm('§cSELL', '', $arrayBtn, function (Player $player, Button $button): void {
                        $target = $button->getText();
                        if (is_null($target)) return;
                        if ($target === '§cRetour') {
                            $this->mainPage($player);
                        } else $this->sellShop($player, $target);
                    });
                    $player->sendForm($form);
                    break;
            }
        } else {
            $player->sendForm(new MenuForm('§eSHOP', '§7Bienvenue sur le shop, vous pouvez vendre et acheter des items en toute simplicité.' . "\n\n§6Votre argent §8: §f" . Main::getInstance()->getEconomyManager()->getMoney($player) . "$", [
                new Button('§l§6» §r§fAcheter', new Image('textures/items/apple')),
                new Button('§l§6» §r§fVendre', new Image('textures/items/diamond'))
            ], function (Player $player, Button $button) use ($shop, $sell) : void {
                $value = $button->getValue();
                if ($value === 0) {
                    $arrayBtn = [];
                    foreach ($shop as $category => $name) {
                        if (isset($name['image'])) {
                            $arrayBtn[] = new Button($name['category_name'], new Image($name['image']));
                        } else {
                            $arrayBtn[] = new Button($name['category_name']);
                        }
                    }
                    $arrayBtn[] = new Button('§cRetour');
                    $form = new MenuForm('§eSHOP', '', $arrayBtn, function (Player $player, Button $button): void {
                        $target = $button->getText();
                        if (is_null($target)) return;
                        if ($target === '§cRetour') {
                            $this->mainPage($player);
                        } else $this->shop($player, $target);
                    });
                    $player->sendForm($form);
                } elseif ($value === 1) {
                    $arrayBtn = [];
                    foreach ($sell as $category => $name) {
                        if (isset($name['image'])) {
                            $arrayBtn[] = new Button($name['category_name'], new Image($name['image']));
                        } else {
                            $arrayBtn[] = new Button($name['category_name']);
                        }
                    }
                    $arrayBtn[] = new Button('§cRetour');
                    $form = new MenuForm('§cSELL', '', $arrayBtn, function (Player $player, Button $button): void {
                        $target = $button->getText();
                        if (is_null($target)) return;
                        if ($target === '§cRetour') {
                            $this->mainPage($player);
                        } else $this->sellShop($player, $target);
                    });
                    $player->sendForm($form);
                } else {
                    $player->sendMessage(Messages::message("§cUne erreur est survenue: 404."));
                }
            }));
        }
    }

    private function shop(Player $player, string $category)
    {
        $shops = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $shops->getAll()['shop'];
        $sell = $shops->getAll()['sell'];


        $arrayBtn = [];
        foreach ($shop[$category]['items'] as $array => $item) {
            if (isset($item['image'])) {
                if (filter_var($item['image'], FILTER_VALIDATE_URL)) {
                    $arrayBtn[] = new Button($item['name'] . "§f - §e" . ($item['buy'] ?? 0) . "$", new Image($item['image'], Image::TYPE_URL));
                } else {
                    $arrayBtn[] = new Button($item['name'] . "§f - §e" . ($item['buy'] ?? 0) . "$", new Image($item['image'], Image::TYPE_PATH));
                }
            } else {
                $arrayBtn[] = new Button($item['name'] . "§f - §e" . ($item['buy'] ?? 0) . "$");
            }
        }
        $arrayBtn[] = new Button("§cRetour");
        $form = new MenuForm('§e' . $category, '', $arrayBtn, function (Player $player, Button $button) use ($category): void {
            $target = $button->getValue();
            if (is_null($target)) return;
            if ($button->getText() === "§cRetour") {
                $this->mainPage($player, 'shop');
            } else {
                $this->buy($player, $category, $target);
            }
        });
        $player->sendForm($form);
    }

    private function buy(Player $player, string $category, $index)
    {
        $shops = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $shops->getAll()['shop'];
        $sell = $shops->getAll()['sell'];

        $itemConfig = $shop[$category]['items'][$index];
        $money = Main::getInstance()->getEconomyManager()->getMoney($player);

        $form = new CustomForm('§e' . strtoupper($itemConfig['name']), [
            new Label("§6Acheter §e:§f " . $itemConfig['buy'] . "\$"),
            new Dropdown("§6Items", [$itemConfig['name']]),
            new Input("§6Quantité", 0)
        ], function (Player $player, CustomFormResponse $response) use ($category, $index, $sell, $shop): void {
            $target = $response->getValues();
            if (is_null($target)) {
                $this->shop($player, $category);
                return;
            }
            if(!is_numeric($target[1]) ||$target[1] <= 0){
                $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                return;
            }

            $itemConfig = $shop[$category]['items'][$index];


            Main::getInstance()->getEconomyManager()->getMoneySQL($player,
                function (Player $player, int $money) use ($target, $itemConfig) : void {
                    if ($money < $itemConfig['buy'] * $target[1]) {
                        $player->sendMessage(Messages::message("§cTu n'a pas assez d'argent."));
                        return;
                    }

                    $itemIdMeta = $itemConfig['idMeta'];


                    if (stripos($itemIdMeta, "goldrush:")) {
                        $item = CustomiesItemFactory::getInstance()->get($itemIdMeta)->setCount($target[1]);
                    } else {
                        $item = StringToItemParser::getInstance()->parse($itemIdMeta)->setCount($target[1]);
                    }
                    if (!$player->getInventory()->canAddItem($item)) {
                        $player->sendMessage(Messages::message("§cTu n'a pas assez de place dans ton inventaire."));
                        return;
                    }

                    $player->getInventory()->addItem($item);
                    $player->sendMessage($this->replace(Messages::message("§aTu à acheté §f{item} §apour §f{price}$ §a!"), [
                        'item' => $itemConfig['name'],
                        'price' => $target[1] * $itemConfig['buy']
                    ]));
                    Main::getInstance()->getEconomyManager()->removeMoney($player, $target[1] * $itemConfig['buy']);
                });
        });

        $player->sendForm($form);
    }

    private function replace(string $str, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $str = str_replace("{" . $key . "}", $value, $str);
        }
        return $str;
    }

    private function sellShop(Player $player, string $category)
    {
        $shops = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $shops->getAll()['shop'];
        $sell = $shops->getAll()['sell'];

        $arrayBtn = [];
        foreach ($sell[$category]['items'] as $array => $item) {
            if (isset($item['image'])) {
                if (filter_var($item['image'], FILTER_VALIDATE_URL)) {
                    $arrayBtn[] = new Button("§f" . $item['name'] . "§f - §e" . ($item['sell'] ?? 0) . "$", new Image($item['image'], Image::TYPE_URL));
                } else {
                    $arrayBtn[] = new Button("§f" . $item['name'] . "§f - §e" . ($item['sell'] ?? 0) . "$", new Image($item['image'], Image::TYPE_PATH));
                }
            } else {
                $arrayBtn[] = new Button("§f" . $item['name'] . "§f - §e" . ($item['sell'] ?? 0) . "$");
            }
        }
        $arrayBtn[] = new Button("§cRetour");
        $form = new MenuForm('§e' . $category, '', $arrayBtn, function (Player $player, Button $button) use ($category, $sell, $shop): void {
            $target = $button->getValue();
            if (is_null($target)) return;
            if ($button->getText() === '§cRetour') {
                $this->mainPage($player, 'sell');
            } else {
                $itemConfig = $sell[$category]['items'][$target];
                if (isset($itemConfig['sell']) && !isset($itemConfig['buy'])) {
                    $this->sell($player, $category, $target);
                } elseif (!isset($itemConfig['sell']) && isset($itemConfig['buy'])) {
                    $this->buy($player, $category, $target);
                } else {
                    $this->buyAndSell($player, $category, $target);
                }
            }
        });
        $player->sendForm($form);
    }

    private function sell(Player $player, string $category, $index)
    {
        $shops = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $shops->getAll()['shop'];
        $sell = $shops->getAll()['sell'];

        $itemConfig = $sell[$category]['items'][$index];
        $itemIdMeta = $itemConfig['idMeta'];
        if (stripos($itemIdMeta, "goldrush:")) {
            $item = CustomiesItemFactory::getInstance()->get($itemIdMeta);
        } else {
            $item = StringToItemParser::getInstance()->parse($itemIdMeta);
        }

        if (!$player->getInventory()->contains($item)) {
            $player->sendMessage(Messages::message("§cVous n'avez pas l'item correspondant."));
            return;
        }

        $form = new CustomForm($itemConfig['name'], [
            new Label("§cVendre : " . $itemConfig['sell'] . "\$"),
            new Dropdown("§6Items", [$itemConfig['name']]),
            new Input("§6Quantité", 0)
        ], function (Player $player, CustomFormResponse $response) use ($category, $index, $sell, $shop): void {
            $target = $response->getValues();
            if (is_null($target) || empty($target)) {
                $this->categoryItems($player, $category);
                return;
            }
            if(!is_numeric($target[1]) ||$target[1] <= 0){
                $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                return;
            }

            $itemConfig = $sell[$category]['items'][$index];
            $itemIdMeta = $itemConfig['idMeta'];
            if (stripos($itemIdMeta, "goldrush:")) {
                $item = CustomiesItemFactory::getInstance()->get($itemIdMeta);
            } else {
                $item = StringToItemParser::getInstance()->parse($itemIdMeta);
            }

            if (!$player->getInventory()->contains($item)) {
                $player->sendMessage(Messages::message("§cVous n'avez pas assez d'item."));
                $this->sendErrorSound($player);
                return;
            }

            $addMoney = $target[1] * $itemConfig['sell'];



            Main::getInstance()->getEconomyManager()->addMoney($player, $addMoney);
            $this->sendSuccessSound($player);
            $player->getInventory()->removeItem($item->setCount($target[1]));
            $player->sendMessage($this->replace(Messages::message("§aVous avez vendu l'item §f{item} §apour §a{price}§a$ !"), [
                'item' => $itemConfig['name'],
                'price' => $target[1] * $itemConfig['sell']
            ]));
        });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param Item $item
     * @return int
     */
    private function getItemInInventory(Player $player, Item $item): int
    {
        $result = array_map(function (Item $invItem) use ($item) {
            if ($invItem->getTypeId() === $item->getTypeId() && $invItem->getStateId() === $item->getStateId()) {
                return $invItem->getCount();
            }
            return 0;
        }, $player->getInventory()->getContents());

        return array_sum($result);
    }

    private function categoryItems(Player $player, string $category)
    {
        $shops = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $shops->getAll()['shop'];
        $sell = $shops->getAll()['sell'];

        $arrayBtn = [];
        foreach ($shop[$category]['items'] as $array => $item) {
            if (isset($item['image'])) {
                if (filter_var($item['image'], FILTER_VALIDATE_URL)) {
                    $arrayBtn[] = new Button("§f" . $item['name'], new Image($item['image'], Image::TYPE_URL));
                } else {
                    $arrayBtn[] = new Button("§f" . $item['name'], new Image($item['image'], Image::TYPE_PATH));
                }
            } else {
                $arrayBtn[] = new Button("§f" . $item['name']);
            }
        }

        $form = new MenuForm('§e' . $category, '', $arrayBtn, function (Player $player, Button $button) use ($category, $shop, $sell): void {
            $target = $button->getValue();
            if (is_null($target)) return;
            $itemConfig = $shop[$category]['items'][$target];
            if (isset($itemConfig['sell']) && !isset($itemConfig['buy'])) {
                $this->sell($player, $category, $target);
            } elseif (!isset($itemConfig['sell']) && isset($itemConfig['buy'])) {
                $this->buy($player, $category, $target);
            } else {
                $this->buyAndSell($player, $category, $target);
            }
        });
        $player->sendForm($form);
    }

    private function buyAndSell(Player $player, string $category, $index)
    {
        $shops = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $shops->getAll()['shop'];
        $sell = $shops->getAll()['sell'];

        $itemConfig = $shop[$category]['items'][$index];
        $form = new CustomForm($itemConfig['name'], [
            new Label("§aAcheter : " . $itemConfig['buy'] . "\$\n§cVendre : " . $itemConfig['sell'] . "\$"),
            new Dropdown("Items", [$itemConfig['name']]),
            new Toggle("§6Acheter§e/§6Vendre", false),
            new Input("Quantité", 0)
        ], function (Player $player, CustomFormResponse $response) use ($category, $index, $shop, $sell): void {
            $target = $response->getValues();
            if (is_null($target) || empty($target)) {
                $this->sellShop($player, $category);
                return;
            }

            if(!is_numeric($target[1]) ||$target[1] <= 0){
                $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                return;
            }

            $itemConfig = $shop[$category]['items'][$index];
            $itemIdMeta = $itemConfig['idMeta'];
            if (stripos($itemIdMeta, "goldrush:")) {
                $item = CustomiesItemFactory::getInstance()->get($itemIdMeta);
            } else {
                $item = StringToItemParser::getInstance()->parse($itemIdMeta);
            }

            if (!$target[1]) {

                Main::getInstance()->getEconomyManager()->getMoneySQL($player,
                    function (Player $player, int $money) use ($target, $itemConfig, $item) : void {
                        if ($money < $itemConfig['buy'] * $target[2]) {
                            $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent."));
                            $this->sendErrorSound($player);
                            return;
                        }
                        if (!$player->getInventory()->canAddItem($item->setCount($target[1]))) {
                            $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                            $this->sendErrorSound($player);
                            return;
                        }
                        $player->getInventory()->addItem($item->setCount($target[1]));
                        $player->sendMessage($this->replace(Messages::message("§aTu à acheté §f{item} §apour §f{price}$ §a!"), [
                            'item' => $itemConfig['name'],
                            'price' => $target[2] * $itemConfig['buy']
                        ]));
                        $this->sendSuccessSound($player);
                        Main::getInstance()->getEconomyManager()->removeMoney($player, $target[2] * $itemConfig['buy']);


                    });



            } else {

                if (!$player->getInventory()->contains($item)) {
                    $player->sendMessage(Messages::message("§cTu n'a pas assez d'item."));
                    $this->sendErrorSound($player);
                    return;
                }

                Main::getInstance()->getEconomyManager()->addMoney($player, $target[2] * $itemConfig['sell']);
                $player->getInventory()->removeItem($item);
                $player->sendMessage($this->replace(Messages::message("§aVous avez vendu l'item §f{item} §apour §a{price}§a$ !"), [
                    'item' => $itemConfig['name'],
                    'price' => $target[2] * $itemConfig['sell']
                ]));
                $this->sendSuccessSound($player);
            }
        });
        $player->sendForm($form);
    }
}