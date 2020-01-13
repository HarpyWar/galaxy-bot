<?php

require_once("Config.php");
require_once("Types.php");
require_once("GalaxyAPI.php");
require_once("GalaxyClient.php");
require_once("GalaxyHelper.php");


$planets_cache = [];
$api_list = [];

while (true)
{
	

	foreach (Config::$Accounts as $i => $a)
	{
		// cache api sessions
		if ( !isset($api_list[$i]) )
		{
			$api_list[$i] = new GalaxyAPI($a);
			$api_list[$i]->Login();
		}
		$api = $api_list[$i];
        $api->log("[" . $a["address"] . "]");

		// get all planets
        $myplanets = $api->GetMyplanets();

		foreach ($myplanets->planets as $tp)
		{
			$api->ChangePlanet($tp->id);
			$p = $api->GetPlanet();
			
			// upgrade all required buildings
			foreach ($p->grids as $g)
			{
				if ($g->level >= 10 || $g->upgrade != null)
					continue;
				if ($g->building_id != BuildingType::Center && 
					$g->building_id != BuildingType::Mine && 
					$g->building_id != BuildingType::Energy && 
					$g->building_id != BuildingType::Cosmoport && 
					$g->building_id != BuildingType::Supply && 
					$g->building_id != BuildingType::Radar && 
					$g->building_id != BuildingType::Trade && 
					$g->building_id != BuildingType::Trainer && 
					$g->building_id != BuildingType::Turret && 
					$g->building_id != BuildingType::Shield)
					continue;


				if (!Config::$UpgradeCenter && $g->building_id == BuildingType::Center)
					continue;
				if (!Config::$UpgradeMine && $g->building_id == BuildingType::Mine)
					continue;
				if (!Config::$UpgradeEnergy && $g->building_id == BuildingType::Energy)
					continue;
				if (!Config::$UpgradeCosmoport && $g->building_id == BuildingType::Cosmoport)
					continue;
				if (!Config::$UpgradeSupply && $g->building_id == BuildingType::Supply)
					continue;
				if (!Config::$UpgradeRadar && $g->building_id == BuildingType::Radar)
					continue;
				if (!Config::$UpgradeTrade && $g->building_id == BuildingType::Trade)
					continue;
				if (!Config::$UpgradeTrainer && $g->building_id == BuildingType::Trainer)
					continue;
				if (!Config::$UpgradeTurret && $g->building_id == BuildingType::Turret)
					continue;
				if (!Config::$UpgradeShield && $g->building_id == BuildingType::Shield)
					continue;
		
				$api->log("upgrade " . $g->building_id);
				$api->Upgrade($g->id);
			}

			if (Config::$Trade)
			{
				$hercules_quantity = 0;
				foreach ($p->units as $u)
				{
					if ($u->id == UnitType::Hercules)
					{
						$hercules_quantity = $u->quantity;
						break;
					}
				}
				// trade
				foreach ($p->resources as $r)
				{
					// minerals per hercules
					$hercules_supply = 100;
					
					// enough to trade
					if ($r->quantity >= $hercules_supply)
					{
						$quantity = $hercules_quantity * $hercules_supply;
						if ($quantity > $r->quantity)
							$quantity = $r->quantity - ($r->quantity % $hercules_supply);
						if ($quantity == 0)
							continue;

						// find trade center
						if ($tc_id = GalaxyHelper::FindTradeCenter($p))
						{
							$api->log("trade " . $r->id . " " . $quantity);
							$api->Trade($r->id, $quantity, $tc_id);
							break;
						}
					}
				}
			}
			
			// send loki
			foreach ($p->units as $u)
			{
				if ($u->id == UnitType::Loki && $u->quantity > 0)
				{
					// find trade center
					if ($tc_id = GalaxyHelper::FindTradeCenter($p))
					{
						$api->log("send loki " . $u->quantity);
						$api->SendUnit($u->id, $u->quantity, $tc_id);
						break;
					}
				}
			}

            // Build required buildings on the planet
            foreach (Config::$RequiredBuildings as $btype => $bcount)
            {
                $count = 0;
                foreach ($p->grids as $g)
                {
                    // building is ready or in-construction
                    if ($g->building_id == $btype || ($g->construction && $g->construction->building_id == $btype))
                        $count++;
                }
                // if no buildings or not enough
                if ($count < $bcount)
                {
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

            // Build all mineral fields on the planet
            foreach ($p->grids as $g)
            {
                if (!$g->building_id && $g->type == GridType::Mineral)
                {
                    $api->log("build mineral");
                    $api->Construct(BuildingType::Mine, $g->id);
                }
            }

            // train loki on the main planet if not enough for two expeditions
            if ($p->is_capital)
            {
                $loki_count = 0;
                // find capital planet to count ships
                foreach ($myplanets->planets as $_p)
                {
                    if ($p->id == $_p->id)
                    {
                        // count loki on the planet (ready and building
                        $loki_count += $_p->ships_count->{UnitType::Loki};
                        $loki_count += $_p->ships_traning->{UnitType::Loki};
                        break;
                    }
                }
                // also count loki on orbital station
                $elist = $api->GetExpeditions();
                foreach ($elist->units as $u)
                {
                    if ($u->id == UnitType::Loki)
                    {
                        $loki_count += $u->quantity;
                        break;
                    }
                }
                // count how many loki are required for a first available expedition
                $first_expedition_loki = 0;
                foreach ($elist->expeditions as $e)
                {
                    foreach ($e->units as $u)
                    {
                        if ($u->id == UnitType::Loki)
                            $first_expedition_loki = $u->quantity;
                        break;
                    }
                    break;
                }
                $loki_diff = $first_expedition_loki * Config::$ExpeditionsForLoki - $loki_count;
                if ($loki_diff > 0)
                {
                    // get count of factories on the planet
                    $factory_count = 0;
                    foreach ($p->grids as $g)
                    {
                        if ($g->building_id == BuildingType::Trade)
                            $factory_count++;
                    }
                    $train_quantity = $loki_diff / $factory_count;
                    // divide builds between available factories
                    foreach ($p->grids as $g)
                    {
                        if ($g->building_id == BuildingType::Trade && !$g->training)
                        {
                            $api->log("train loki " . $train_quantity);
                            $api->Train(UnitType::Loki, $train_quantity, $g->id);
                        }
                    }
                }
            }
		}




        // #######################
		// Orbital Station
		
		// check expeditions
		$elist = $api->GetExpeditions();
		foreach ($elist->expeditions as $e)
		{
			$available = false;
			foreach ($e->units as $u1)
			{
				$available = false;
				foreach ($elist->units as $u2)
				{
					if ($u2->id != $u1->id)
						continue;
					if ($u2->quantity >= $u1->quantity)
						$available = true;
				}
				if (!$available)
					break;
			}
			if ($available)
			{
				$api->log("expedition " . $e->id);
				$api->CompleteExpedition($e->id);
				// complete only single available, cause we spend units for this
				break;
			}
		}
		
		// check missions
		$mlist = $api->GetMissions();
		foreach ($mlist->missions as $m)
		{
			$available = false;
			foreach ($m->resources as $u1)
			{
				$available = false;
				foreach ($mlist->resources as $u2)
				{
					if ($u2->id != $u1->id)
						continue;
					if ($u2->quantity >= $u1->quantity)
						$available = true;
				}
				if (!$available)
					break;
			}
			if ($available)
			{
				$api->log("mission " . $m->id);
				$api->CompleteMission($m->id);
				// complete only single available, cause we spend resources for this
				break;
			}
		}
	}
	
	#echo "sleep 60 seconds...\n";
	#sleep(60);
}


