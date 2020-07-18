<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;

class TradeHandler extends PlanetHandler
{
    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;


        if (!Config::$Trade)
            return;

        $hercules_quantity = 0;
        foreach ($p->units as $u)
        {
            if ($u->id == UnitType::Hercules)
            {
                $hercules_quantity = $u->quantity;
                break;
            }
        }
        // for orbital station also count hercules from there
        if ($p->is_capital)
        {
            $elist = $api->GetExpeditions();
            foreach ($elist->units as $u)
            {
                if ($u->id == UnitType::Hercules)
                    $hercules_quantity += $u->quantity;
            }
        }
        // trade
        foreach ($p->resources as $r)
        {
            // minerals per hercules
            $hercules_supply = 1000;

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
}