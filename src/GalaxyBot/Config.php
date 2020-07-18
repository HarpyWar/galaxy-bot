<?php

namespace GalaxyBot;

use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\UnitType;

require_once("GalaxyAPI.php");
require_once("GalaxyClient.php");
require_once("GalaxyHelper.php");
require_once("PlanetCache.php");


class Config
{
    // set on start if $UseProxyFile exists
    public static $UseProxy = false;
    public static $UseProxyFile = "use_proxy";

    // delay between accounts (seconds)
    public static $Delay = 30;

	public static $Trade = true;

    // upgrade can be set to false or to level from 1 to 10
    public static $UpgradeBuildings = [
        [
            "min_energy" => 1,
            "min_planets" => 1,
            "min_level" => 1,
            "data" => [
                BuildingType::Center => 5,
                BuildingType::Mine => 10,
                BuildingType::Energy => 5,
                BuildingType::Cosmoport => 3,
                BuildingType::Supply => 3, // not required very high if trade every minute and it close to the main planet
                BuildingType::Radar => 2,
                BuildingType::Trade => 10,
                BuildingType::Trainer => 5,
                BuildingType::Turret => 2,
                BuildingType::Shield => 3
            ]
        ],
        [
            "min_energy" => 50000,
            "is_capital" => true, // only for capital
            "data" => [
                BuildingType::Trainer => 10,
            ]
        ],
        [
            "min_energy" => 100000,
            "data" => [
                BuildingType::Center => 10,
                BuildingType::Mine => 10,
                BuildingType::Energy => 10,
                BuildingType::Cosmoport => 5,
                BuildingType::Trade => 10,
                BuildingType::Trainer => 10,
                BuildingType::Turret => 5,
                BuildingType::Shield => 5
            ]
        ],
        [
            "min_energy" => 500000,
            "data" => [
                BuildingType::Mine => 10,
                BuildingType::Energy => 10,
                BuildingType::Cosmoport => 7,
                BuildingType::Radar => 8,
                BuildingType::Trade => 10,
                BuildingType::Trainer => 8,
                BuildingType::Turret => 7,
                BuildingType::Shield => 8
            ]
        ],
        [
            "min_energy" => 1000000,
            "data" => [
                BuildingType::Cosmoport => 10,
                BuildingType::Supply => 7, // not required very high if trade every minute and it close to the main planet
                BuildingType::Radar => 10,
                BuildingType::Trade => 10,
                BuildingType::Trainer => 10,
                BuildingType::Turret => 10,
                BuildingType::Shield => 10
            ]
        ]
    ];

    // Train execute only after all other builds and upgrades
    // "data" should contain any items but first 5 will be in priority because of 5 factories max
    // (all unit types must be unique inside "data" of each item)
    public static $ConstructUnits = [
        [
            "min_energy" => 1,
            "min_planets" => 1,
            "min_level" => 1,
            "data" => [
            ]
        ],
        [
            "min_energy" => 1000000,
            "min_planets" => 1,
            "min_level" => 1,
            "data" => [
                UnitType::Hornet => 1,
                UnitType::Excalibur => 1,
            ]
        ],
    ];

    // construct these buildings on empty planet
    public static $RequiredBuildings = [
        BuildingType::Center => 1,      // max 1
        BuildingType::Energy => 1, 
     // max 1
        BuildingType::Cosmoport => 4,
        //BuildingType::Supply => 1, // FIXME: do not build supply because if shield was killed and there are no supply then shield will not be built
        BuildingType::Radar => 1,       // max 1
        BuildingType::Trade => 1,
        BuildingType::Trainer => 5,     // max 5
		BuildingType::Turret => 1,
        BuildingType::Shield => 1,       // max 1
    ];

	// fill empty grids with the following building type
	public static $FillEmptyGridBuilding = BuildingType::Turret;

    // sell only greater than this value for each type of resources
    public static $ResourceSellLimit = 1000000;

	/*
	public static $UpgradeCenter = true;
	public static $UpgradeMine = true;
	public static $UpgradeEnergy = true;
	public static $UpgradeCosmoport = true;
	public static $UpgradeSupply = false; // do not required if trade every minute
	public static $UpgradeRadar = true;
	public static $UpgradeTrade = true;
	public static $UpgradeTrainer = true;
	public static $UpgradeTurret = true;
	public static $UpgradeShield = true;
	*/

	// min user energy we should convert minerals to energy on full capacity planets
    public static $MinEnergyToConvert = 5000;

	// train 1 units per a time
	public static $HerculesTrainCount = 2;
    public static $LokiTrainCount = 10;
    // other units train coint (1 is good)
    public static $UnitTrainCount = 1;

    // Optimal quantity of hercules for orbital station
    // (actually any value should be ok, 100 is normal for any level, it may be good less if planets are low level)
    public static $MinHerculesOrbitalCount = 10;

    // create loki on the main planet for this count of expeditions
    // (x3 should be good, x2 on orbital station and x1 training)
    public static $ExpeditionsForLoki = 3;

    /*
    // keep units count on the planet not less than defined
    // (including flying)
    public static $MinUnitsOnPlanet = [
        // WARN: do not put loki, cause they will be send
        //       immediately on orbital station and start build again
        UnitType::Hercules => 10
    ];
    */

    // account => token
	public static $Accounts = [
		[
			"address" => "", // login
			"password" => "",
            "ip" => "" // proxy ip (for several network interfaces)
		]
	];
}

