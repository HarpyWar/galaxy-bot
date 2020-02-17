<?php

namespace GalaxyBot\Types;

class MineralType
{
    const Torium = 1;
    const Vanadium = 2;
    const Otarium = 3;
    const Chromium = 4;
    const Cladium = 5;
    const Neodium = 6;
    const Minterium = 7;
    const Solarium = 8;

    // just array of all available minerals
    public static $All = [
        self::Torium,
        self::Vanadium,
        self::Otarium,
        self::Chromium,
        self::Cladium,
        self::Neodium,
        self::Minterium,
        self::Solarium
    ];
}
