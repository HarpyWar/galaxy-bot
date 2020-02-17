<?php

namespace GalaxyBot;
use GalaxyBot\Config;

use GalaxyBot\Handlers;

/**
 * Class Planet scope
 */
class Planet
{
    private $handlers = [];

    /**
     * @var Account
     */
    private $account;

    public $p;

    public function __construct(Account $account)
    {
        $this->account = $account;
        // define all handlers here
        $this->handlers = [
            new Handlers\Minerals2EnergyHandler($this, $account),
            new Handlers\TrainHerculesHandler($this, $account),
            new Handlers\BuildHandler($this, $account),
            new Handlers\TrainLokiHandler($this, $account),
            new Handlers\UpgradeHandler($this, $account),
            new Handlers\TradeHandler($this, $account),
            new Handlers\UnitTransferHandler($this, $account),

            // after all
            new Handlers\ExpeditionsHandler($this, $account),
            new Handlers\MissionsHandler($this, $account),
            new Handlers\TrainUnitsHandler($this, $account),
            new Handlers\AccountExportHandler($this, $account),
            new Handlers\UnitActionHandler($this, $account),
            new Handlers\SellOrbitalResourceHandler($this, $account),
            new Handlers\PlanetShieldHandler($this, $account),
            //...
        ];
    }

    public function Handle($pl)
    {
        #$this->account->api->log("switch -> " . $pl->display_name);
        $this->account->api->ChangePlanet($pl->id);

        // preload general data
        $this->p = $this->account->api->GetPlanet();

        // also update myplanets
        $this->account->myplanets = $this->account->api->GetMyPlanets();

        // update planet cache
        $this->account->cache->set($this->p->id, "id", $this->p->id);
        $this->account->cache->set($this->p->id, "display_name", $this->p->display_name);
        $this->account->cache->set($this->p->id, "grids", $this->p->grids);
        $this->account->cache->set($this->p->id, "is_capital", $this->p->is_capital);
        $this->account->cache->set($this->p->id, "x", $this->p->x);
        $this->account->cache->set($this->p->id, "y", $this->p->y);

        foreach ($this->handlers as $h)
        {
            try
            {
                $h->Execute();
            }
            catch(\Exception $e)
            {
                $this->account->api->log($e->getMessage());
            }
        }
    }

}

