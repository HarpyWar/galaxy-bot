<?php

namespace GalaxyBot;

use GalaxyBot\Config;
use GalaxyBot\GalaxyAPI;
use GalaxyBot\PlanetCache;

/**
 * Class Account scope
 */
class Account
{
    /**
     * @var GalaxyApi
     */
    public $api;

    /**
     * @var PlanetCache
     */
    public $cache;

    /**
     * @var string Account name
     */
    public $address;

    // dynamic data
    public $myplanets;
    public $user;
    public $capital;

    public $expeditions;
    public $missions;

    public function __construct($api, $cache, $address)
    {
        $this->api = $api;
        $this->address = $address;
        $this->cache = $cache;

        $this->loadData();
    }

    private function loadData()
    {
        $this->user = $this->api->GetUser();
        $this->capital = $this->api->GetCapital();
        $this->myplanets = $this->api->GetMyPlanets();

        $this->expeditions = $this->api->GetExpeditions();
        $this->missions = $this->api->GetMissions();
    }

}

