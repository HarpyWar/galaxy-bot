<?php

namespace GalaxyBot;
use GalaxyBot\Config;

class GalaxyHelper
{
	// 100%
	const InitSpeed = 2;
	const HerculesSpeed = 2;
	const HerculesCapacity = 100; // minerals
	
	public static function FindBuilding($buildingType, $grids)
	{
		foreach ($grids as $g)
		{
			if ($g->building_id == $buildingType)
			{
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
}
