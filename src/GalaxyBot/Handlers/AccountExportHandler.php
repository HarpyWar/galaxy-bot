<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;

class AccountExportHandler extends PlanetHandler
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


        // #########################################
        $units = [];
        $planets = [];
        // upgrade all required buildings
        foreach ($myplanets->planets as $mp)
        {
            $planets[] = $mp->name;


            foreach ($mp->ships_count as $u => $quantity)
            {
                if (!array_key_exists($u, $units))
                    $units[$u] = 0;
                $units[$u] += $quantity;
            }
            foreach ($mp->ships_traning as $u => $quantity)
            {
                if (!array_key_exists($u, $units))
                    $units[$u] = 0;
                $units[$u] += $quantity;
            }
        }

        $export = [
            'username' => $user->username,
            'solarion' => $myplanets->solarion,
            'energy' => $user->energy,
            'level' => $user->level,
            'solarion' => $myplanets->solarion,
            'units' => $units,
            'planet_count' => count($planets),
            'planets' => $planets,
        ];

        $filename = "accounts/" . $user->username . "_" . count($planets) . "_" . $user->energy . ".txt";
        file_put_contents($filename, json_encode($export, JSON_PRETTY_PRINT));
    }
}