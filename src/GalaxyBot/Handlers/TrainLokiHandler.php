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
class TrainLokiHandler extends PlanetHandler
{
    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;
        $cache = $this->account->cache;
        $myplanets = $this->account->myplanets;
        $expeditions = $this->account->expeditions;

        // train loki on the main planet if not enough for two expeditions
        if (!$p->is_capital)
            return;

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
        foreach ($expeditions->units as $u)
        {
            if ($u->id == UnitType::Loki)
            {
                $loki_count += $u->quantity;
                break;
            }
        }
        // count how many loki are required for a first available expedition
        $first_expedition_loki = 0;
        foreach ($expeditions->expeditions as $e)
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
}