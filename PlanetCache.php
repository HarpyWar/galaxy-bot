<?php



class PlanetCache
{
    protected $address = false;
    protected $cached = [];
    protected $values = array();

    public function setAccount($address)
    {
        $this->address = $address;
    }

    // return true if all planets on the account are cached
    public function isCached()
    {
        return isset($this->cached[$this->address]);
    }
    // set flag cached
    public function setCached()
    {
        $this->cached[$this->address] = true;
    }

    // return all planets cache from the account
    public function planets()
    {
        return isset($this->values[$this->address])
            ? $this->values[$this->address]
            : [];
    }

    // set cache key=val for the planet
    public function set($pid, $key, $val)
    {
        if (!isset($this->values[$this->address][$pid]))
            $this->values[$this->address][$pid] = [];
        $this->values[$this->address][$pid][$key] = $val;
    }

    // get cached key for the planet
    public function get($pid, $key)
    {
        if (!isset($this->values[$this->address][$pid]) || !isset($this->values[$this->address][$pid][$key]))
            return false;
        return $this->values[$this->address][$pid][$key];
    }

}