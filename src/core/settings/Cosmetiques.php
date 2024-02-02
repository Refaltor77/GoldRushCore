<?php

namespace core\settings;

/*
 * 1000 800 : common
 * 800 500 : rare
 * 500 100 : epic
 * 100 0 : legend
 */


interface Cosmetiques
{
    const ALL = [
        CosmeticsIds::AUREOLE => 1000,
        CosmeticsIds::PEACH => 900,
        CosmeticsIds::BANDANA_BLACK => 999,
        CosmeticsIds::BANDANA_BLUE => 988,
        CosmeticsIds::BANDANA_GOLD => 977,
        CosmeticsIds::BANDANA_GREEN => 966,
        CosmeticsIds::BANDANA_PURPLE => 955,
        CosmeticsIds::BANDANA_RED => 944,
        CosmeticsIds::BANDANA_WHITE => 933,
        CosmeticsIds::CASQUE_AUDIO => 922,
        CosmeticsIds::LUNETTE_NOIR => 911,
        CosmeticsIds::TODD => 912,


        CosmeticsIds::BLUE_BOB => 700,
        CosmeticsIds::BLACK_BOB => 750,
        CosmeticsIds::RED_BOB => 650,
        CosmeticsIds::GREEN_BOB => 740,
        CosmeticsIds::PURPLE_BOB => 730,
        CosmeticsIds::LUIGI => 620,
        CosmeticsIds::MARIO => 610,
        CosmeticsIds::WITCH => 745,
        CosmeticsIds::CORNE_CERF => 746,
        CosmeticsIds::BLACK_HAT => 744,
        CosmeticsIds::WHITE_HAT => 743,


        CosmeticsIds::URAHARA_KEISUKE_BOB => 444,
        CosmeticsIds::CASQUETTE_JOTARO => 455,
        CosmeticsIds::CASQUETTE_MARIN => 433,
        CosmeticsIds::TROLL => 423,

        CosmeticsIds::CK_BOB => 50,
        CosmeticsIds::BARBE_NOEL => 11,
        CosmeticsIds::NAPOLEON => 10,
        CosmeticsIds::COWBOYHAT => 10,





        CosmeticsIds::BACKPACK_BLEU => 910,
        CosmeticsIds::BACKPACK_ROUGE => 909,
        CosmeticsIds::BACKPACK_VIOLET => 908,
        CosmeticsIds::BACKPACK => 907,
        CosmeticsIds::FLEUR_VIOLET => 906,
        CosmeticsIds::YOSHI => 905,
        CosmeticsIds::GODZILLA => 904,
        CosmeticsIds::GOLDBAG => 903,
        CosmeticsIds::JETPACK => 902,
        CosmeticsIds::PAIN_DEPICE => 901,


        CosmeticsIds::AILES_DRAGON => 742,
        CosmeticsIds::AILES_NOIR => 741,
        CosmeticsIds::BOWSER => 740,
        CosmeticsIds::JETPACK_BLANC => 902,
        CosmeticsIds::JETPACK_BLEU => 902,
        CosmeticsIds::JETPACK_JAUNE => 902,
        CosmeticsIds::JETPACK_NOIR => 902,
        CosmeticsIds::JETPACK_ROSE => 902,


        CosmeticsIds::CHAUSETTE_NOEL => 500,
        CosmeticsIds::SABRE => 495,
        CosmeticsIds::GANT_DE_BOXE => 494,
        CosmeticsIds::EPEE_CROISEE_BLEU => 499,
        CosmeticsIds::EPEE_CROISEE_VIOLET => 498,
        CosmeticsIds::EPEE_CROISEE_ROUGE_FLAMME => 497,
        CosmeticsIds::EPEE_CROISEE_TOXIQUE => 496,
        CosmeticsIds::JETPACK_VERT => 902,
        CosmeticsIds::JETPACK_VIOLET => 902,
        CosmeticsIds::JETPACK_DORE=> 902,


        CosmeticsIds::BACKPACK_OR => 9,
        CosmeticsIds::TORTUE_GENIAL => 8,
    ];




    const HEADS = [
        CosmeticsIds::AUREOLE => 1000,
        CosmeticsIds::PEACH => 900,
        CosmeticsIds::BANDANA_BLACK => 999,
        CosmeticsIds::BANDANA_BLUE => 988,
        CosmeticsIds::BANDANA_GOLD => 977,
        CosmeticsIds::BANDANA_GREEN => 966,
        CosmeticsIds::BANDANA_PURPLE => 955,
        CosmeticsIds::BANDANA_RED => 944,
        CosmeticsIds::BANDANA_WHITE => 933,
        CosmeticsIds::CASQUE_AUDIO => 922,
        CosmeticsIds::LUNETTE_NOIR => 911,
        CosmeticsIds::TODD => 912,



        CosmeticsIds::BLUE_BOB => 700,
        CosmeticsIds::BLACK_BOB => 750,
        CosmeticsIds::RED_BOB => 650,
        CosmeticsIds::GREEN_BOB => 740,
        CosmeticsIds::PURPLE_BOB => 730,
        CosmeticsIds::LUIGI => 620,
        CosmeticsIds::MARIO => 610,
        CosmeticsIds::WITCH => 745,
        CosmeticsIds::CORNE_CERF => 746,

        CosmeticsIds::BLACK_HAT => 744,
        CosmeticsIds::WHITE_HAT => 743,


        CosmeticsIds::URAHARA_KEISUKE_BOB => 444,
        CosmeticsIds::CASQUETTE_JOTARO => 455,
        CosmeticsIds::CASQUETTE_MARIN => 433,
        CosmeticsIds::TROLL => 423,

        CosmeticsIds::CK_BOB => 50,
        CosmeticsIds::BARBE_NOEL => 11,
        CosmeticsIds::NAPOLEON => 10,
        CosmeticsIds::COWBOYHAT => 10,
    ];

    const CAPE_BLACKLIST = [
        "cape_alpha",
        "cape_beta",
        "cape_bugs",
        "cape_bugs_beta",
        "cape_bugs_goldrush",
    ];

    const CAPES = [
        "cape_alpha",
        "cape_beta",
        "cape_bugs",
        "cape_bugs_beta",
        "cape_bugs_goldrush",
        "cape_goldrush",
        "cape_love",
        "cape_noel"
    ];


    const BACK = [
        CosmeticsIds::BACKPACK_BLEU => 910,
        CosmeticsIds::BACKPACK_ROUGE => 909,
        CosmeticsIds::BACKPACK_VIOLET => 908,
        CosmeticsIds::BACKPACK => 907,
        CosmeticsIds::FLEUR_VIOLET => 906,
        CosmeticsIds::YOSHI => 905,
        CosmeticsIds::GODZILLA => 904,
        CosmeticsIds::GOLDBAG => 903,
        CosmeticsIds::JETPACK => 902,
        CosmeticsIds::PAIN_DEPICE => 901,


        CosmeticsIds::AILES_DRAGON => 742,
        CosmeticsIds::AILES_NOIR => 741,
        CosmeticsIds::BOWSER => 740,
        CosmeticsIds::JETPACK_BLANC => 902,
        CosmeticsIds::JETPACK_BLEU => 902,
        CosmeticsIds::JETPACK_JAUNE => 902,
        CosmeticsIds::JETPACK_NOIR => 902,
        CosmeticsIds::JETPACK_ROSE => 902,


        CosmeticsIds::CHAUSETTE_NOEL => 500,
        CosmeticsIds::EPEE_CROISEE_BLEU => 499,
        CosmeticsIds::EPEE_CROISEE_VIOLET => 498,
        CosmeticsIds::EPEE_CROISEE_ROUGE_FLAMME => 497,
        CosmeticsIds::EPEE_CROISEE_TOXIQUE => 496,
        CosmeticsIds::JETPACK_VERT => 902,
        CosmeticsIds::JETPACK_VIOLET => 902,
        CosmeticsIds::JETPACK_DORE=> 902,


        CosmeticsIds::BACKPACK_OR => 9,
        CosmeticsIds::TORTUE_GENIAL => 8,

    ];
    const OTHERS = [
        CosmeticsIds::GANT_DE_BOXE => 494,
        CosmeticsIds::SABRE => 495,
    ];
    const PETS = [];
    const COSTUMES = [];
}