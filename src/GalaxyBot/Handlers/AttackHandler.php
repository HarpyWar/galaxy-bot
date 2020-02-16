<?php

namespace GalaxyBot\Handlers;

use GalaxyBot;
use GalaxyBot\Config;
use GalaxyBot\GalaxyHelper;
use GalaxyBot\Types\UnitType;
use GalaxyBot\Types\BuildingType;
use GalaxyBot\Types\GridType;
use GalaxyBot\Types\MineralType;
use PHPUnit\TextUI\Help;

class AttackHandler extends PlanetHandler
{
    // without a slash at the end
    private $path = "attack_queue";

    public function Execute()
    {
        if (!parent::Execute())
            return;

        // shorten names
        $p = $this->planet->p;
        $user = $this->account->user;
        $api = $this->account->api;
        $myplanets = $this->account->myplanets;

        $queue = $this->readQueue($p->id, $api);
        foreach ($queue as $q)
        {
            if (isset($q->units))
            {
                // support "0" value to use all available units for action
                foreach ($myplanets->planets as $mp)
                {
                    if ($mp->id != $q->source_planet_id)
                        continue;
                    foreach ($mp->ships_count as $k1 => $v1)
                    {
                        foreach ($q->units->quantity as $k2 => $v2)
                        {
                            if ($k1 != $k2)
                                continue;
                            // replace 0 to max
                            if ($v2 == 0)
                                $q->units->quantity->{$k2} = $v1;
                        }
                    }
                    break;
                }
            }

            $result = false;
            if ($q->action == 0)
                $result = $api->SupportPlanet($q->units, $q->target_planet_id);
            if ($q->action == 1)
                $result = $api->AttackPlanet($q->units, $q->target_planet_id);
            if ($q->action == 2)
                $result = $api->OccupyPlanet($q->target_planet_id);


            $txt_action = $q->action == 0
                ? "support"
                : $q->action == 1
                    ? "attack"
                    : "occupy";
            $api->log($txt_action . " planet " . $q->target_planet_id . " " . ($result ? "successful" : "failed"));
            // a second between attacks
            sleep(1);
        }
    }

    /*
     * Read local requests and delete files with them
     */
    private function readQueue($planetId, $api)
    {
        $queue = [];
        // Open a directory, and read its contents
        if (!is_dir($this->path))
            return $queue;
        if (!($dh = opendir($this->path)))
            return $queue;
        while (($file = readdir($dh)) !== false)
        {
            $filepath = $this->path . '/' . $file;
            if ($file == '.' || $file == '..' || is_dir($filepath))
                continue;
            try
            {
                $data = file_get_contents($filepath);
                $json = json_decode($data);

                // add only targets with current planet
                if ($json->source_planet_id != $planetId)
                    continue;
                $queue[] = $json;
                // delete file
                unlink($filepath);
            }
            catch (\Exception $e)
            {
                $api->log("file incorrect " . $file);
            }
        }
        closedir($dh);
        return $queue;
    }
}