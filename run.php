<?php

namespace GalaxyBot;

$loader = require __DIR__.'/vendor/autoload.php';
$loader->add('GalaxyBot', __DIR__.'/src');

use GalaxyBot\GalaxyAPI;

$app = new GalaxyApp();
$app->Run();


class GalaxyApp
{
    /**
     * @var GalaxyAPI[]
     */
    private $api_list = [];

    public function Run()
    {
        while (true)
        {
            $this->iterateAccounts();
        }
    }

    private function iterateAccounts()
    {
        foreach (Config::$Accounts as $i => $a)
        {
            // cache api sessions
            if ( !isset($this->api_list[$i]) )
            {
                $this->api_list[$i] = new GalaxyAPI($a);
                $this->api_list[$i]->Login();
            }
            $api = $this->api_list[$i];
            $api->log("[" . $a["address"] . "]");

            // account scope
            $account = new Account($api, $a["address"]);
            // itarate account planets
            $this->handlePlanets($account);
        }
    }

    public function handlePlanets(Account $a)
    {
        // [!important] handle capital first
        foreach ($a->myplanets->planets as $p)
        {
            if ($p->id == $a->capital->capital_id)
            {
                $planet = new Planet($a);
                $planet->Handle($p);
                break;
            }
        }
        // then handle other planets
        foreach ($a->myplanets->planets as $p)
        {
            if ($p->id == $a->capital->capital_id)
                continue;
            $planet = new Planet($a);
            $planet->Handle($p);
        }

        $a->cache->setCached();
        foreach ($a->cache->planets() as $c)
        {
            if (!isset($c["herc_count"]))
                continue;
            $a->api->log($c["display_name"] . " Hercules: " . $c["herc_count"] . " / " . $c["herc_opt"] . " / " . ($c["herc_count"] - $c["herc_opt"]));
        }
    }
}







