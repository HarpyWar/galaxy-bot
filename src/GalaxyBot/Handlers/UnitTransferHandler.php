<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;

class UnitTransferHandler extends PlanetHandler
{
    public function Execute()
    {
        parent::Execute();

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;

        // send loki and valkyries to orbital station
        foreach ($p->units as $u)
        {
            if ($u->id != UnitType::Loki) //  && $u->id != UnitType::Valkyrie
                continue;
            if ($u->quantity == 0)
                continue;
            // find trade center
            if ($tc_id = GalaxyHelper::FindBuilding(BuildingType::Trade, $p->grids))
            {
                $api->log("send " . $u->id . " (" . $u->quantity . ")");
                $api->SendUnit($u->id, $u->quantity, $tc_id);
                break;
            }
        }
    }
}