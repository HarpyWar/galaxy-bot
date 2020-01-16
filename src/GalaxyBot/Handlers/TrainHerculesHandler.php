<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;

/**
 * Move  hercules between planets and if not enough then train new
 */
class TrainHerculesHandler extends PlanetHandler
{
    public function Execute()
    {
        parent::Execute();

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;
        $cache = $this->account->cache;
        $myplanets = $this->account->myplanets;


        if (!($radar_id = GalaxyHelper::FindBuilding(BuildingType::Radar, $p->grids)))
            return;

        $herc_count = 0;

        // cache radar data for all planets of the account, because it's large
        if ( !isset($this->account->cache->radar[$radar_id]) )
            $this->account->cache->radar[$radar_id] = $api->GetRadarMovements($radar_id);
        $moves = $this->account->cache->radar[$radar_id];

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
                if ( !($_grids = $cache->get($m->start->id, "grids")) )
                    continue;
                $_radar_id = GalaxyHelper::FindBuilding(BuildingType::Radar, $_grids);
                if ( !isset($this->account->cache->radar[$_radar_id]) )
                    $this->account->cache->radar[$_radar_id] = $api->GetRadarMovements($_radar_id);
                $_moves = $this->account->cache->radar[$_radar_id];

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
        foreach ($cache->planets() as $c)
        {
            if (!$c["is_capital"])
                continue;
            $orbital_x = $c["x"];
            $orbital_y = $c["y"];
        }

        // optimal hercules quantity
        $herc_opt = GalaxyHelper::CalcOptimalHerculesCount($p->mining_rate, $p->x, $p->y, $orbital_x, $orbital_y);
        if ($p->is_capital)
            $herc_opt = Config::$OrbitalHerculesOptimalCount;
        $herc_diff = $herc_count - $herc_opt; // ! important order

        // if all planets are cached
        if ($cache->isCached())
        {
            // if we need more hercules
            if ($herc_diff < 0)
            {
                $supported = false;
                // ####################################################
                // a) iterate through other planets in the cache and
                //    find from where we can transfer some hercules
                foreach ($cache->planets() as $cp)
                {
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
                    $cache->set($cp["id"], "herc_count", $cp["herc_count"] - $support_quantity);
                    $supported = true;

                    $herc_count += $support_quantity;
                    $herc_diff += $support_quantity;
                    // do not find other planets if the goal reached
                    if ($herc_diff >= 0)
                        break;
                }

                // ####################################################
                // b) build new hercules if not enough on other planets
                if (!$supported) {
                    // get count of factories on the planet
                    $factory_count = 0;
                    foreach ($p->grids as $g) {
                        if ($g->building_id == BuildingType::Trainer)
                            $factory_count++;
                    }

                    // divide builds between available factories
                    foreach ($p->grids as $g) {
                        if ($herc_diff >= 0)
                            break;

                        if ($g->building_id == BuildingType::Trainer && !$g->training) {
                            $train_quantity = abs(ceil($herc_diff / $factory_count));
                            if ($train_quantity == 0)
                                $train_quantity = $herc_diff;

                            $quantity = $train_quantity < Config::$HerculesTrainCount
                                ? $train_quantity
                                : Config::$HerculesTrainCount;
                            $api->log("train " . $quantity . " hercules (required " . $herc_diff . ")");
                            $api->Train(UnitType::Hercules, $quantity, $g->id);

                            // subtract
                            $herc_diff += $quantity;
                        }
                    }
                }
            }
        }

        $cache->set($p->id, "herc_count", $herc_count);
        $cache->set($p->id, "herc_opt", $herc_opt);
    }
}