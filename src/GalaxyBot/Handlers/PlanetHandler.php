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

    // 'planet' run once per planet
    // 'user' - run once per account
    protected $scope = 'planet';

    public function __construct(Planet $planet, Account $account)
    {
        $this->planet = $planet;
        $this->account = $account;
    }

    public function Execute()
    {
        // for account scope run only once (for capital planet)
        if ($this->scope == 'user' && !$this->planet->p->is_capital)
            return false;
        #$this->account->api->log("exec " . get_class($this));
        return true;
    }
}