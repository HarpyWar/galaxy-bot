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

    // train 1 unit at once, all units = 100%, then divide between factories
    // on the plane
    // Train execute only after all other builds and upgrades
    // "data" should contain max 5 items (1 for each factory)
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
                UnitType::Hornet => 3,
                UnitType::Javeline => 1,
                UnitType::Excalibur => 2,
                UnitType::Valkyrie => 2
            ]
        ],
    ];

    // construct these buildings on empty planet
    public static $RequiredBuildings = [
        BuildingType::Center => 1,      // max 1
        BuildingType::Energy => 1,      // max 1
        BuildingType::Cosmoport => 4,
        BuildingType::Supply => 1,
        BuildingType::Radar => 1,       // max 1
        BuildingType::Trade => 1,
        BuildingType::Trainer => 5,     // max 5
        BuildingType::Shield => 1,       // max 1
        // set last order and count of 6 (if not all fields busy then build
        BuildingType::Turret => 7
    ];

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
			"address" => "Mxfda2f0143fbc896c6dac4fa50714661f93187b44",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.71"
		],
		[
			"address" => "Mx1ba993008a62d7be98b33f92bd85ba1c6a3fdb84",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.72"
		],
		[
			"address" => "Mxabf98d720d1df5e3a639648507b7e87fb2204370",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.73"
		],
		[
			"address" => "Mx111e470b470d418503a78c4e6bbb0816213c8fa2",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.74"
		],
		[
			"address" => "Mx0d516c3cf0fa4cae2c25bc85d756d338be798bc3",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.75"
		],
		[
			"address" => "Mxc57f77767f20954b306c6e098d1a06fe893f65ce",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.76"
		],
		[
			"address" => "Mx7372ce22560dd2501b6ccd58a82ca90e9f53942d",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.77"
		],
		[
			"address" => "Mx12a2b73be1e1ae9db1dd86fbea97875ea74ad6a7",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.78"
		],
		[
			"address" => "Mx9fb3048e865a7f6472427d6db5ef42486f31c2dd",
			"password" => "klmgaTp795fRlORSCjqw",
            "ip" => "5.9.194.79"
		]
	];
}

