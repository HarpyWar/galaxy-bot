<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;

class UpgradeHandler extends PlanetHandler
{
    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;

        // upgrade all required buildings
        foreach ($p->grids as $g)
        {
            if (!$g->building_id || $g->construction || $g->upgrade)
                continue;
            if ($g->level >= 10)
                continue;

            // calc building upgrade level depending on user energy etc
            $upgrade_level = GalaxyHelper::GetUpgradeQuantity($g->building_id,
                                $user->energy,
                                $user->level,
                                count($p->planets),
                                $p->is_capital);
            if ($g->level > $upgrade_level)
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
    }
}