<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Config;
use GalaxyBot\Handlers;

use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;

class BuildHandler extends PlanetHandler
{
    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;


		$construction = false;
        // Build required buildings on the planet
        foreach (Config::$RequiredBuildings as $btype => $req_count)
        {
            $cur_count = 0;
            foreach ($p->grids as $g)
            {
                // building is ready or in-construction
                if ($g->building_id == $btype || ($g->construction && $g->construction->building_id == $btype))
                    $cur_count++;
            }
            // if no buildings or not enough
            if ($cur_count < $req_count)
            {
				$construction = true;
                foreach ($p->grids as $g)
                {
                    // ignore non-empty slots
                    if ($g->type != GridType::Empty || $g->building_id || $g->construction)
                        continue;
                    $api->log("build " . $btype);
                    $api->Construct($btype, $g->id);
                    // FIXME: break parent cycle, because we busy slot for construction
                    //        next construction will be done on next account handle
                    break 2;
                }
            }
        }

		// fill empty slots
		if (!$construction)
		{
			foreach ($p->grids as $g)
			{
				// ignore non-empty slots
				if ($g->type != GridType::Empty || $g->building_id || $g->construction)
					continue;
				$api->log("build extra " . Config::$FillEmptyGridBuilding);
				$api->Construct(Config::$FillEmptyGridBuilding, $g->id);
			}
		}
		
        // Build all mineral fields on the planet
        foreach ($p->grids as $g)
        {
            if (!$g->building_id && $g->type == GridType::Mineral)
            {
                $api->log("build mineral");
                $api->Construct(BuildingType::Mine, $g->id);
            }
        }

    }
}