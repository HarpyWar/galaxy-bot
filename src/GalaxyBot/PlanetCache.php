<?php

namespace GalaxyBot;
use GalaxyBot\Config;

class PlanetCache
{
    /**
     * @var Account
     */
    protected $a;
    protected $cached = [];
    protected $values = [];

    public $radar = [];

    public function __construct()
    {
    }

    public function setAccount($account)
    {
        $this->a = $account;
    }

    // return true if all planets on the account are cached
    public function isCached()
    {
        return isset($this->cached[$this->a->address]);
    }
    // set flag cached
    public function setCached()
    {
        $this->cached[$this->a->address] = true;
    }

    // return all planets cache from the account
    public function planets()
    {
        return isset($this->values[$this->a->address])
            ? $this->values[$this->a->address]
            : [];
    }

    // set cache key=val for the planet
    public function set($pid, $key, $val)
    {
        if (!isset($this->values[$this->a->address][$pid]))
            $this->values[$this->a->address][$pid] = [];
        $this->values[$this->a->address][$pid][$key] = $val;
    }

    // get cached key for the planet
    public function get($pid, $key)
    {
        if (!isset($this->values[$this->a->address][$pid]) || !isset($this->values[$this->a->address][$pid][$key]))
            return false;
        return $this->values[$this->a->address][$pid][$key];
    }


}