<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;
use PHPUnit\TextUI\Help;

class SellOrbitalResourceHandler extends PlanetHandler
{
    protected $scope = 'user';

    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;
        $myplanets = $this->account->myplanets;

        $missions = $this->account->missions;

        // get resources on orbital station
        foreach ($missions->resources as $r)
        {
            // sell reduntant minerals
            if ($r->quantity <= Config::$ResourceSellLimit)
                continue;

            $sell_quantity = $r->quantity - Config::$ResourceSellLimit;
            if ($prod_id = GalaxyHelper::FindBuilding(BuildingType::Energy, $p->grids))
            {
                $api->log("sell resource " . $r->id . " " . $sell_quantity);
                $api->SellResource($r->id, $sell_quantity, $prod_id);
            }
        }
    }
}