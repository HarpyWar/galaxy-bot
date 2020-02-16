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
        self::Hercules, // 1
        self::Loki,     // 2
        self::Raptor,   // 3
        self::Hornet,   // 4
        self::Javeline, // 5
        self::Excalibur,// 6
        self::Valkyrie, // 7
        self::Titan     // 8
    ];
}