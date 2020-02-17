<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Common;
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
        $expeditions = $this->account->expeditions;
        $cache = $this->account->cache;

        // #########################################
        $units = [];
        $planets = [];
        $planet_resources = [];
        $mining_rate = [];
        // fill empty array with available minerals
        foreach (MineralType::$All as $m)
        {
            $mining_rate[$m] = 0;
            $planet_resources[$m] = 0;
        }

        // upgrade all required buildings
        foreach ($myplanets->planets as $mp) {
            $planets[] = [
                'id' => $mp->id,
                'name' => $mp->name,
                'resource_id' => $mp->resource_id,
                'units' => $mp->ships_count
            ];

            foreach ($mp->ships_count as $u => $quantity) {
                if (!array_key_exists($u, $units))
                    $units[$u] = 0;
                $units[$u] += $quantity;
            }
            foreach ($mp->ships_traning as $u => $quantity) {
                if (!array_key_exists($u, $units))
                    $units[$u] = 0;
                $units[$u] += $quantity;
            }
            $planet_resources[$mp->resource_id]++;
            if ($cache->isCached())
            {
                $mining_rate[$mp->resource_id] += $cache->get($p->id, "mining_rate");
            }
        }
        // also count units on orbital station
        foreach ($expeditions->units as $u)
        {
            if (!array_key_exists($u->id, $units))
                $units[$u->id] = 0;
            $units[$u->id] += $u->quantity;
        }

        $export = [
            'username' => $user->username,
            'solarion' => $myplanets->solarion,
            'energy' => $user->energy,
            'level' => $user->level,
            'production_rate' => $user->production_rate, // energy per a hour
            'solarion' => $myplanets->solarion,
            'units' => $units,
            'planet_count' => count($planets),
            'planets' => $planets,
            'planet_resources' => $planet_resources
        ];

        if ($cache->isCached())
        {
            $export['mining_rate'] = $mining_rate; // minerals per a hour
        }

        $path = 'accounts';

        // delete old files for the account
        foreach (new \DirectoryIterator($path) as $f) {
            if (Common::StartsWith($f->getFilename(), $user->username))
                unlink($path . '/' . $f->getFilename());
        }

        $filename = $path . '/' . $user->username . "_" . count($planets) . "_" . $user->energy . ".txt";
        file_put_contents($filename, json_encode($export, JSON_PRETTY_PRINT));
    }
}