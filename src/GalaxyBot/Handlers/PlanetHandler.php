<?php

namespace GalaxyBot\Handlers;

use GalaxyBot\Config;
use GalaxyBot\Planet;
use GalaxyBot\Account;

class PlanetHandler
{
    /**
     * @var Planet
     */
    protected $planet;
    protected $account;

    public function __construct(Planet $planet, Account $account)
    {
        $this->planet = $planet;
        $this->account = $account;
    }

    public function Execute()
    {
        #$this->account->api->log("exec " . get_class($this));
    }
}