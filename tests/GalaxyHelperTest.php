<?php

namespace GalaxyBot;

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('GalaxyBot', __DIR__.'/../src');


use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\UnitType;

class GalaxyHelperTest extends \PHPUnit\Framework\TestCase
{

    public function test_itemQuantity1()
    {
        $UpgradeBuildings = [
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
                    BuildingType::Energy => 9,
                    BuildingType::Cosmoport => 5,
                    BuildingType::Trade => 10,
                    BuildingType::Trainer => 10,
                ]
            ],
            [
                "min_energy" => 1000000,
                "data" => [
                    BuildingType::Center => 10,
                    BuildingType::Mine => 10,
                    BuildingType::Energy => 10,
                    BuildingType::Cosmoport => 10,
                    BuildingType::Supply => 9, // not required very high if trade every minute and it close to the main planet
                    BuildingType::Radar => 10,
                    BuildingType::Trade => 10,
                    BuildingType::Trainer => 10,
                    BuildingType::Turret => 10,
                    BuildingType::Shield => 10
                ]
            ]
        ];
        $is_capital = false;

        $buildingType = BuildingType::Energy;
        $userEnergy = 1;
        $userLevel = 1;
        $userPlanets = 1;
        $value = GalaxyHelper::GetItemQuantity($UpgradeBuildings, $buildingType, $userEnergy, $userLevel, $userPlanets, $is_capital);
        $this->assertEquals(5, $value);

        $buildingType = BuildingType::Trainer;
        $userEnergy = 50000;
        $userLevel = 1;
        $userPlanets = 1;
        $value = GalaxyHelper::GetItemQuantity($UpgradeBuildings, $buildingType, $userEnergy, $userLevel, $userPlanets, true);
        $this->assertEquals(10,$value);

        $buildingType = BuildingType::Energy;
        $userEnergy = 100000;
        $userLevel = 2;
        $userPlanets = 1;
        $value = GalaxyHelper::GetItemQuantity($UpgradeBuildings, $buildingType, $userEnergy, $userLevel, $userPlanets, $is_capital);
        $this->assertEquals(9,$value);

        $buildingType = BuildingType::Energy;
        $userEnergy = 1000000;
        $userLevel = 1;
        $userPlanets = 1;
        $value = GalaxyHelper::GetItemQuantity($UpgradeBuildings, $buildingType, $userEnergy, $userLevel, $userPlanets, $is_capital);
        $this->assertEquals(10,$value);
    }


    public function test_itemQuantity2()
    {

        // train 1 unit at once, all units = 100%, then divide between factories
        // on the plane
        // Train execute only after all other builds and upgrades
        // "data" should contain max 5 items (1 for each factory)
        $ConstructUnits = [
            [
                "min_energy" => 1000000,
                "min_planets" => 1,
                "min_level" => 1,
                "data" => [
                    UnitType::Hornet => 1,
                    UnitType::Javeline => 1,
                    UnitType::Excalibur => 1,
                    UnitType::Valkyrie => 1
                ]
            ],
        ];
        $is_capital = false;

        $unitType = UnitType::Hornet;
        $userEnergy = 1;
        $userLevel = 1;
        $userPlanets = 1;
        $value = GalaxyHelper::GetItemQuantity($ConstructUnits, $unitType, $userEnergy, $userLevel, $userPlanets, $is_capital);
        $this->assertEquals(0, $value);

        $unitType = UnitType::Hornet;
        $userEnergy = 1000000;
        $userLevel = 1;
        $userPlanets = 1;
        $value = GalaxyHelper::GetItemQuantity($ConstructUnits, $unitType, $userEnergy, $userLevel, $userPlanets, $is_capital);
        $this->assertEquals(1, $value);

        $unitType = UnitType::Hornet;
        $userEnergy = 1000000;
        $userLevel = 0;
        $userPlanets = 1;
        $value = GalaxyHelper::GetItemQuantity($ConstructUnits, $unitType, $userEnergy, $userLevel, $userPlanets, $is_capital);
        $this->assertEquals(0, $value);

        $unitType = UnitType::Hornet;
        $userEnergy = 1000000;
        $userLevel = 1;
        $userPlanets = 0;
        $value = GalaxyHelper::GetItemQuantity($ConstructUnits, $unitType, $userEnergy, $userLevel, $userPlanets, $is_capital);
        $this->assertEquals(0, $value);

    }

}