<?php

namespace GalaxyBot\Handlers;

use GalaxyBot\Handlers;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;

/**
 * Move  hercules between planets and if not enough then train new
 */
class MissionsHandler extends PlanetHandler
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
        $cache = $this->account->cache;
        $myplanets = $this->account->myplanets;
        $missions = $this->account->missions;

        // check missions
        foreach ($missions->missions as $m)
        {
            $available = false;
            foreach ($m->resources as $u1)
            {
                $available = false;
                foreach ($missions->resources as $u2)
                {
                    if ($u2->id != $u1->id)
                        continue;
                    if ($u2->quantity >= $u1->quantity)
                        $available = true;
                }
                if (!$available)
                    break;
            }
            if ($available)
            {
                $api->log("mission " . $m->id);
                $api->CompleteMission($m->id);
                // complete only single available, cause we spend resources for this
                break;
            }
        }
    }
}