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
class ExpeditionsHandler extends PlanetHandler
{
    protected $scope = 'account';

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

        foreach ($expeditions->expeditions as $e)
        {
            $available = false;
            foreach ($e->units as $u1)
            {
                $available = false;
                foreach ($expeditions->units as $u2)
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
                $api->log("expedition " . $e->id);
                $api->CompleteExpedition($e->id);
                // complete only single available, cause we spend units for this
                break;
            }
        }

    }
}