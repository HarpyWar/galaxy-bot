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
class TrainUnitsHandler extends PlanetHandler
{
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


        // get free factories on the planet
        $factories = [];
        foreach ($p->grids as $g)
        {
            if ($g->building_id != BuildingType::Trainer || $g->training)
                continue;
            array_push($factories, $g->id);
        }

        for ($i = 0; $i < count(UnitType::$All); $i++)
        {
            $type = UnitType::$All[$i];
            // how much build this unit in a single factory
            $quantity = GalaxyHelper::GetUnitQuantity($type,
                            $user->energy,
                            $user->level,
                            count($p->planets),
                            $p->is_capital);
            if ($quantity > 0)
            {
                // if no factories available then exit cycle
                if ( !($grid_id = array_pop($factories)) )
                    break;

                $api->log("train unit " . $type);
                $api->Train($type, $quantity, $grid_id);
            }

            // cycle only if there are still factories available
            if ($i == count(UnitType::$All) - 1 && count($factories) > 0)
                $i = 0;
        }
    }
}