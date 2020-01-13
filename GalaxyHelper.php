<?php


class GalaxyHelper
{
	public static function FindTradeCenter($planet)
	{
		$p = $planet;
		foreach ($p->grids as $g)
		{
			if ($g->building_id == BuildingType::Trade)
			{
				return $g->id;
			}
		}
		return false;
	}
	
	
}