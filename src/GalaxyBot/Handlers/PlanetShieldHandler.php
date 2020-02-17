<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;

/*
 * Activate protection shield if planet is under valkyries + titan attack
 * and BuildingType::Shield not exists on the planet
 * Radar should detect these types of units, so we should not set shield if valkyries
 * are coming and we have a shield
 *
 */
class PlanetShieldHandler extends PlanetHandler
{
    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;
        $monitor = $this->account->monitor;
        $myplanets = $this->account->myplanets;

        if ($mp = $this->findPlanet($myplanets, $p->id))
        {
            // if protection shield already activated on the planet then do nothing
            if ($mp->shield_time)
                return;
        }


        $valkyries = 0;
        $titans = 0;
        foreach ($monitor->incoming_movements as $m)
        {
            // handle incomings only for the current planet
            if ($m->end->id != $p->id)
                continue;
            // start planet must be enemy and end planet - ours
            if ($this->findPlanet($myplanets, $m->start->id) || !$this->findPlanet($myplanets, $m->end->id))
                continue;

            // count total valkyries and titans
            if ( isset($m->ships) )
            {
                if ( isset($m->ships->{UnitType::Valkyrie}) )
                    $valkyries += $m->ships->{UnitType::Valkyrie};
                if ( isset($m->ships->{UnitType::Titan}) )
                    $titans += $m->ships->{UnitType::Titan};
            }
        }

        // if shield generator not found
        if (!GalaxyHelper::FindBuilding(BuildingType::Shield, $p->grids))
        {
            if ($titans && $valkyries)
            {
                $api->log("set protection shield to the planet  " . $p->id);
                $api->SetProtectionShield($p->id);
            }
        }
    }

    private  function findPlanet($myplanets, $pid)
    {
        foreach ($myplanets->planets as $mp)
        {
            if ($mp->id == $pid)
                return $mp;
        }
        return false;
    }

}