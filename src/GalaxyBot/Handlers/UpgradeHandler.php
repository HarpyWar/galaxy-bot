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
    }
}