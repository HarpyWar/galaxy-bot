<?php

namespace GalaxyBot\Types;

class UnitType
{
    const Hercules = 1;
    const Loki = 2;
    const Raptor = 3;
    const Hornet = 4;
    const Javeline = 5;
    const Excalibur = 6;
    const Valkyrie = 7;
    const Titan = 8;

    // just array of all available units
    public static $All = [
        self::Hercules,
        self::Loki,
        self::Raptor,
        self::Hornet,
        self::Javeline,
        self::Excalibur,
        self::Valkyrie,
        self::Titan
    ];
}