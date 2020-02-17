<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;


class SellPlanetResourceHandler extends PlanetHandler
{
    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;

        // If planet minerals are full, and not enough energy - sell half of minerals from the planet supply
        if ($p->used_capacity == $p->capacity && $user->energy < Config::$MinEnergyToConvert)
        {
            if ($energy_id = GalaxyHelper::FindBuilding(BuildingType::Energy, $p->grids))
            {
                $api->log("convert " . $p->capacity . " minerals to energy");
                $api->SellResource($p->resource_id, $p->capacity, $energy_id);
            }
        }
    }
}