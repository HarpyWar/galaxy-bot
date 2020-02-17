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
    private $supply_limit_percents = 95; // %

    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;

        // how many units we must send to orbital station, because of full supply (95% filled)
        $send_quantity = 0;
        $supply_limit = $p->supply / 100 * $this->supply_limit_percents;
        if ($p->used_supply > $supply_limit)
        {
            $send_quantity = $p->used_supply - $supply_limit;
            $api->log($this->supply_limit_percents . "% supply used, we must send " . $send_quantity . " units to orbital");
        }

        // iterate every type of units
        foreach ($p->units as $u)
        {
            if ($u->id == UnitType::Hercules)
                continue;

            $quantity = $u->quantity;
            if ($quantity == 0)
                continue;

            // for not green, yellow and blue planets - transfer all units except hercules
            if ($send_quantity <= 0)
            {
                if ($p->resource_id > MineralType::Otarium)
                {
                    if ($u->id != UnitType::Loki && $u->id != UnitType::Valkyrie)
                        continue;
                }
            }
            else
            {
                // send only required units
                // 10 - 100 = -90
                // 100 + (-90) = 10
                $send_quantity -= $quantity;
                if ($send_quantity <= 0)
                    $quantity += $send_quantity;
            }

            // find trade center
            if ($tc_id = GalaxyHelper::FindBuilding(BuildingType::Trade, $p->grids))
            {
                $api->log("send " . $u->id . " (" . $quantity . ")");
                $api->SendUnit($u->id, $quantity, $tc_id);
            }
        }
    }
}