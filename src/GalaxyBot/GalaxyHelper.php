<?php

namespace GalaxyBot;
use GalaxyBot\Config;

class GalaxyHelper
{
	// 100%
	const InitSpeed = 2;
	const HerculesSpeed = 2;
	const HerculesCapacity = 100; // minerals

    /*
     * $includeInConstruction - if true then include buildings which are in construiction mode (not ready yet)
     */
	public static function FindBuilding($buildingType, $grids, $includeInConstruction = true)
	{
		foreach ($grids as $g)
		{
			if ($g->building_id == $buildingType)
			{
			    if (!$g->construction || ($g->construction && $includeInConstruction))
				    return $g->id;
			}
		}
		return false;
	}

	/*
	 * Path length between two planets
	 */
	public static function PathLen($x1, $y1, $x2, $y2)
	{
		$x = abs($x1 - $x2);
		$y = abs($y1 - $y2);
		$len = sqrt(pow($x, 2) + pow($y, 2));
		if ($len < 0)
			$len = 0;
		return ceil($len);
	}

	/*
	 * How many seconds ship with a given speed is required to finish the journay
	 */
	public static function PathTime($path_len, $speed)
	{
		$time = ceil($path_len / ($speed / self::InitSpeed));
		// FIXME: it always subtract ~1 minute
		$time -= 60;
		if ($time < 0)
			$time = 0;
		return $time;
	}
	
	
	/*
	 * Calculate optimal quantity of hercules on the planet to mine minerals and send to orbital station in cycle
	 */
	public static function CalcOptimalHerculesCount($income_per_hour, $planet_x, $planet_y, $orbital_x, $orbital_y)
	{
		$income_per_second = $income_per_hour / 60 / 60;
		$mineral_percent = 1 / 100;
		$percents_per_second = $income_per_second / $mineral_percent;
		$mine_time = (1 / $percents_per_second) * 100; // 1 mineral mine time in seconds
		$path_len = self::PathLen($planet_x, $planet_y, $orbital_x, $orbital_y);
		$path_time = self::PathTime($path_len, self::HerculesSpeed); 
	    $path_time *= 2; // go + forward

		$count = ceil($path_time / (self::HerculesCapacity * $mine_time));
				
		// FIXME: add everal ships to faster transfer already existing minerals
		$count += 5;
		
		return intval($count);
	}

	public static function GetUnitQuantity($unitType, $userEnergy, $userLevel, $userPlanetCount, $planet_capital)
    {
        return self::getItemQuantity(Config::$ConstructUnits, $unitType, $userEnergy, $userLevel, $userPlanetCount, $planet_capital);
    }

    public static function GetUpgradeQuantity($buildingType, $userEnergy, $userLevel, $userPlanetCount, $planet_capital)
    {
        return self::getItemQuantity(Config::$UpgradeBuildings, $buildingType, $userEnergy, $userLevel, $userPlanetCount, $planet_capital);
    }

    public static function GetItemQuantity($items, $itemKey, $userEnergy, $userLevel, $userPlanetCount, $planet_capital)
    {
        $value = 0;
        foreach ($items as $it)
        {
            if ( !array_key_exists("data", $it) || count($it["data"]) == 0)
                continue;
            if ( !array_key_exists($itemKey, $it["data"]) )
                continue;
            $is_capital = isset($it["is_capital"]) ? true : false;
            if ($is_capital && !$planet_capital)
                continue;
            $min_energy = isset($it["min_energy"]) ? $it["min_energy"] : 0;
            if ($userEnergy < $min_energy)
                continue;
            $min_planets = isset($it["min_planets"]) ? $it["min_planets"] : 0;
            if ($userPlanetCount < $min_planets)
                continue;
            $min_level = isset($it["min_level"]) ? $it["min_level"] : 0;
            if ($userLevel < $min_level)
                continue;
            $value = $it["data"][$itemKey];
        }
        return $value;
    }

}
