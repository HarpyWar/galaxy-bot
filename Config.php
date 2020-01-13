<?php

require_once("Types.php");

class Config
{
	public static $Trade = true;

	// upgrade can be set to false or to level from 1 to 10
	public static $UpgradeCenter = false;
	public static $UpgradeMine = 10;
	public static $UpgradeEnergy = false;
	public static $UpgradeCosmoport = false;
	public static $UpgradeSupply = 5; // not required very high if trade every minute and it close to the main planet
	public static $UpgradeRadar = false;
	public static $UpgradeTrade = false;
	public static $UpgradeTrainer = false;
	public static $UpgradeTurret = false;
	public static $UpgradeShield = false;


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

    // construct these buildings on empty planet
	public static $RequiredBuildings = [
        BuildingType::Center => 1,      // max 1
        BuildingType::Energy => 1,      // max 1
        BuildingType::Cosmoport => 2,
        BuildingType::Supply => 2,
        BuildingType::Radar => 1,       // max 1
        BuildingType::Trade => 1,
        BuildingType::Trainer => 5,     // max 5
        BuildingType::Turret => 5,
        BuildingType::Shield => 1       // max 1
    ];

	
	// account => token
	public static $Accounts = [
		[
			"address" => "Mxfda2f0143fbc896c6dac4fa50714661f93187b44",
			"password" => "klmgaTp795fRlORSCjqw"
		],
		[
			"address" => "Mx1ba993008a62d7be98b33f92bd85ba1c6a3fdb84",
			"password" => "klmgaTp795fRlORSCjqw"
		],
		[
			"address" => "Mxabf98d720d1df5e3a639648507b7e87fb2204370",
			"password" => "klmgaTp795fRlORSCjqw"
		],
		[
			"address" => "Mx111e470b470d418503a78c4e6bbb0816213c8fa2",
			"password" => "klmgaTp795fRlORSCjqw"
		],
		[
			"address" => "Mx0d516c3cf0fa4cae2c25bc85d756d338be798bc3",
			"password" => "klmgaTp795fRlORSCjqw"
		],
		[
			"address" => "Mxc57f77767f20954b306c6e098d1a06fe893f65ce",
			"password" => "klmgaTp795fRlORSCjqw"
		],
		[
			"address" => "Mx7372ce22560dd2501b6ccd58a82ca90e9f53942d",
			"password" => "klmgaTp795fRlORSCjqw"
		],
		[
			"address" => "Mx12a2b73be1e1ae9db1dd86fbea97875ea74ad6a7",
			"password" => "klmgaTp795fRlORSCjqw"
		],
		[
			"address" => "Mx9fb3048e865a7f6472427d6db5ef42486f31c2dd",
			"password" => "klmgaTp795fRlORSCjqw"
		]
	];
}

