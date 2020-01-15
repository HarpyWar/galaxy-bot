<?php

require_once("Config.php");
require_once("Types.php");
require_once("GalaxyAPI.php");
require_once("GalaxyClient.php");
require_once("GalaxyHelper.php");

// cache ids etc
$planet_cache = new PlanetCache();
$api_list = [];

while (true)
{
	foreach (Config::$Accounts as $i => $a)
	{
        $planet_cache->setAccount($a["address"]);

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
        $user = $api->GetUser();

		foreach ($myplanets->planets as $tp)
		{
			$api->ChangePlanet($tp->id);
			$p = $api->GetPlanet();
			
			
			// create hercules only if cache is ready
            // and the planet is not orbital
            // and radar exists
			if ($planet_cache->isCached() &&
                !$p->is_capital &&
                ($radar_id = GalaxyHelper::FindBuilding(BuildingType::Radar, $p->grids)))
            {
                $herc_count = 0;

                $moves = $api->GetRadarMovements($radar_id);
                // we always see outgoing units from the same planet
                foreach ($moves->outgoing_movements as $m)
                {
                    // ignore movements started from other planets
                    if ($m->start->id != $p->id)
                        continue;
                    foreach ($m->units as $u)
                    {
                        if ($u->id == UnitType::Hercules)
                            $herc_count += $u->quantity;
                    }
                }

                $radar_cache = [];
                $last_radar_id = 0;
                foreach ($moves->incoming_movements as $m)
                {
                    // ignore movements with destination to other planets
                    if ($m->end->id != $p->id)
                        continue;

                    if (count($m->units))
                    {
                        foreach ($m->units as $u)
                        {
                            if ($u->id == UnitType::Hercules)
                                $herc_count += $u->quantity;
                        }
                    }
                    // if we dont see units on the radar
                    // then fetch radar from every planet and watch there
                    else
                    {
                        // get source planet buildings from the cache
                        if ( !($_grids = $planet_cache->get($m->start->id, "grids")) )
                            continue;
                        $_radar_id = GalaxyHelper::FindBuilding(BuildingType::Radar, $_grids);
                        if ( !isset($radar_cache[$_radar_id]) )
                            $radar_cache[$_radar_id] = $api->GetRadarMovements($_radar_id);
                        $_moves = $radar_cache[$_radar_id];

                        foreach ($_moves->outgoing_movements as $_m)
                        {
                            // find only single movement
                            if ($_m->id != $m->id)
                                continue;
                            // ignore
                            foreach ($_m->units as $u)
                            {
                                if ($u->id == UnitType::Hercules)
                                    $herc_count += $u->quantity;
                            }
                            break;
                        }
                    }
                }

                $orbital = false;
                // increase units from the planet
                foreach ($myplanets->planets as $mp)
                {
                    if ($p->id != $mp->id)
                        continue;

                    // count hercules on the planet (ready and building)
                    $herc_count += $mp->ships_count->{UnitType::Hercules};
                    $herc_count += $mp->ships_traning->{UnitType::Hercules};
                }
                $orbital_x = $p->x;
                $orbital_y = $p->y;
                // also find orbital station
                foreach ($planet_cache->planets() as $c)
                {
                    if (!$c["is_capital"])
                        continue;
                    $orbital_x = $c["x"];
                    $orbital_y = $c["y"];
                }

                // optimal hercules quantity
                $herc_opt = GalaxyHelper::CalcOptimalHerculesCount($p->mining_rate, $p->x, $p->y, $orbital_x, $orbital_y);
                $herc_diff = $herc_count - $herc_opt; // ! important order

                // if we need more hercules
                if ($herc_diff < 0)
                {
                    $supported = false;
                    // ####################################################
                    // a) iterate through other planets in the cache and
                    //    find from where we can transfer some hercules
                    foreach ($planet_cache->planets() as $cp)
                    {
                        // planey may not be cached fully yet, so ignore such items
                        if (!isset($cp["herc_count"]))
                            continue;

                        $_herc_diff = $cp["herc_count"] - $cp["herc_opt"]; // ! important order
                        // ignore planets where not enough hercules
                        if ($_herc_diff < 0)
                            continue;

                        // how much units we can support
                        #$support_quantity = $_herc_diff > abs($herc_diff)
                        #    ? abs($herc_diff)
                        #    : $_herc_diff;
                        // FIXME: support with a small quantity, because all units can not be on the planet now
                        $support_quantity = Config::$HerculesTrainCount;

                        $api->ChangePlanet($cp["id"]); // switch on the planet where we have reduntant units
                        // send units
                        $api->log($cp["display_name"] . " support " . $support_quantity . " hercules to " . $p->display_name . " (required " . $herc_diff . ")");
                        $api->SupportUnit(UnitType::Hercules, $support_quantity, $p->id);
                        $api->ChangePlanet($p->id); // switch back

                        // update cache
                        $planet_cache->set($cp["id"], "herc_count", $cp["herc_count"] - $support_quantity);
                        $supported = true;

                        $herc_count += $support_quantity;
                        $herc_diff += $support_quantity;
                        // do not find other planets if the goal reached
                        if ($herc_diff >= 0)
                            break;
                    }

                    // ####################################################
                    // b) build new hercules if not enough on other planets
                    if (!$supported)
                    {
                        // get count of factories on the planet
                        $factory_count = 0;
                        foreach ($p->grids as $g)
                        {
                            if ($g->building_id == BuildingType::Trainer)
                                $factory_count++;
                        }
                        $train_quantity = ceil($herc_diff / $factory_count);
                        // divide builds between available factories
                        foreach ($p->grids as $g)
                        {
                            if ($g->building_id == BuildingType::Trainer && !$g->training)
                            {
                                $quantity = $train_quantity < Config::$HerculesTrainCount
                                    ? $train_quantity
                                    : Config::$HerculesTrainCount;
                                $api->log("train " . $quantity . " hercules (required " . $herc_diff . ")");
                                //$api->Train(UnitType::Hercules, $quantity, $g->id);
                            }
                        }
                    }
                }

                $planet_cache->set($p->id, "herc_count", $herc_count);
                $planet_cache->set($p->id, "herc_opt", $herc_opt);
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
                        if ($g->building_id == BuildingType::Trainer)
                            $factory_count++;
                    }
                    #$train_quantity = ceil($loki_diff / $factory_count);
                    // FIXME: train several units because energy is low usually
                    $train_quantity = Config::$LokiTrainCount;
                    // divide builds between available factories
                    foreach ($p->grids as $g)
                    {
                        if ($g->building_id == BuildingType::Trainer && !$g->training)
                        {
                            $api->log("train loki " . $train_quantity);
                            $api->Train(UnitType::Loki, $train_quantity, $g->id);
                        }
                    }
                }
            }
			
			
			
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


				if ((!Config::$UpgradeCenter || $g->level > Config::$UpgradeCenter) && $g->building_id == BuildingType::Center)
					continue;
				if ((!Config::$UpgradeMine || $g->level > Config::$UpgradeMine) && $g->building_id == BuildingType::Mine)
					continue;
				if ((!Config::$UpgradeEnergy || $g->level > Config::$UpgradeEnergy) && $g->building_id == BuildingType::Energy)
					continue;
				if ((!Config::$UpgradeCosmoport || $g->level > Config::$UpgradeCosmoport) && $g->building_id == BuildingType::Cosmoport)
					continue;
				if ((!Config::$UpgradeSupply || $g->level > Config::$UpgradeSupply) && $g->building_id == BuildingType::Supply)
					continue;
				if ((!Config::$UpgradeRadar || $g->level > Config::$UpgradeRadar) && $g->building_id == BuildingType::Radar)
					continue;
				if ((!Config::$UpgradeTrade || $g->level > Config::$UpgradeTrade) && $g->building_id == BuildingType::Trade)
					continue;
				if ((!Config::$UpgradeTrainer || $g->level > Config::$UpgradeTrainer) && $g->building_id == BuildingType::Trainer)
					continue;
				if ((!Config::$UpgradeTurret || $g->level > Config::$UpgradeTurret) && $g->building_id == BuildingType::Turret)
					continue;
				if ((!Config::$UpgradeShield || $g->level > Config::$UpgradeShield) && $g->building_id == BuildingType::Shield)
					continue;

				// check for enough energy
				$upg_info = $api->UpgradeInfo($g->id);
				if ($user->energy >= $upg_info->upgrade->construction_cost)
                {
                    $api->log("upgrade " . $g->building_id);
                    $api->Upgrade($g->id);
                    $user->energy -= $upg_info->upgrade->construction_cost;
                }
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
						if ($tc_id = GalaxyHelper::FindBuilding(BuildingType::Trade, $p->grids))
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
					if ($tc_id = GalaxyHelper::FindBuilding(BuildingType::Trade, $p->grids))
					{
						$api->log("send loki " . $u->quantity);
						$api->SendUnit($u->id, $u->quantity, $tc_id);
						break;
					}
				}
			}

            $planet_cache->set($p->id, "id", $p->id);
            $planet_cache->set($p->id, "display_name", $p->display_name);
            $planet_cache->set($p->id, "grids", $p->grids);
            $planet_cache->set($p->id, "is_capital", $p->is_capital);
            $planet_cache->set($p->id, "x", $p->x);
            $planet_cache->set($p->id, "y", $p->y);
		}
        $planet_cache->setCached();
		foreach ($planet_cache->planets() as $c)
        {
            if (!isset($c["herc_count"]))
                continue;
            $api->log($c["display_name"] . " Hercules: " . $c["herc_count"] . " / " . $c["herc_opt"] . " / " . ($c["herc_count"] - $c["herc_opt"]));
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


class PlanetCache
{
    protected $address = false;
    protected $cached = [];
    protected $values = array();

    public function setAccount($address)
    {
        $this->address = $address;
    }

    // return true if all planets on the account are cached
    public function isCached()
    {
        return isset($this->cached[$this->address]);
    }
    // set flag cached
    public function setCached()
    {
        $this->cached[$this->address] = true;
    }

    // return all planets cache from the account
    public function planets()
    {
        return $this->values[$this->address];
    }

    // set cache key=val for the planet
    public function set($pid, $key, $val)
    {
        if (!isset($this->values[$this->address][$pid]))
            $this->values[$this->address][$pid] = [];
        $this->values[$this->address][$pid][$key] = $val;
    }

    // get cached key for the planet
    public function get($pid, $key)
    {
        if (!isset($this->values[$this->address][$pid]) || !isset($this->values[$this->address][$pid][$key]))
            return false;
        return $this->values[$this->address][$pid][$key];
    }

}