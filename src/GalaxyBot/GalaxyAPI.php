<?php

namespace GalaxyBot;

use GalaxyBot\Config;

class GalaxyAPI
{
	public $client;
	private $account;
	private $planet_name = "unknown";
	
	public function __construct($account)
	{
		$this->account = $account;
		$this->client = new GalaxyClient($this);
	}
	
	public function Login()
	{
		$this->client->Auth($this->account["address"], $this->account["password"], $this->account["ip"]);
	}
	
	public function ChangePlanet($planetId)
	{
		return $this->client->Put("/user/current/" . $planetId);
	}

	/*
	 * Get data of the selected planet
	 */
	public function GetPlanet()
	{
		$response = $this->client->Get("/planet");
        $this->planet_name = $response->display_name;
		return $response;
	}
	
	public function GetMyPlanets()
	{
		return $this->client->Get("/myplanets");
	}
    public function GetUser()
    {
        return $this->client->Get("/user");
    }
    public function GetCapital()
    {
        return $this->client->Get("/user/capital");
    }

	public function GetExpeditions()
	{
		return $this->client->Get("/expedition");
	}
	public function GetMissions()
	{
		return $this->client->Get("/mission");
	}
	/*
	 * General radar alerts
	 */
    public function GetMonitor()
    {
        return $this->client->Get("/monitor/show");
    }

	public function GetRadarMovements($gridId)
	{
		return $this->client->Get("/scout/" . $gridId);
	}

    public function SetProtectionShield($planetId)
    {
        return $this->client->Post("/shield/" . $planetId, []);
    }
	
	public function Trade($mineralType, $quantity, $gridId)
	{
		$data = [
			"quantity" => [
				$mineralType => $quantity
			]
		];
		return $this->client->Post("/movement/trade/" . $gridId, $data);
	}

	public function Train($unitType, $quantity, $gridId)
	{
        $data = [
            "quantity" => $quantity
        ];
		return $this->client->Post("/trainer/" . $gridId . "/" . $unitType, $data);
	}

	// convert minerals to energy
    public function SellResource($mineralType, $quantity, $gridId)
    {
        $data = [
            "quantity" => $quantity
        ];
        return $this->client->Post("/producer/" . $gridId . "/" . $mineralType, $data);
    }


    // Construct building
	public function Construct($buildingType, $gridId)
	{
		return $this->client->Post("/construction/" . $gridId . "/" . $buildingType, false);
	}	
	
	public function UpgradeInfo($gridId)
	{
		return $this->client->Get("/upgrade/" . $gridId);
	}
	public function Upgrade($gridId)
	{
		return $this->client->Post("/upgrade/" . $gridId, false);
	}
	
	public function SendUnit($unitType, $quantity, $gridId)
	{
		$data = [
			"quantity" => [
				$unitType => $quantity
			]
		];
		return $this->client->Post("/movement/patrol/" . $gridId, $data);
	}


	/*
	 * Explore planet with loki
	 */
    public function ExplorePlanet($quantity, $planetId)
    {
        $data = [
            "quantity" => $quantity
        ];
        return $this->client->Post("/movement/scout/" . $planetId, $data);
    }

	/*
	 * $units = {"quantity":{"5":1, "6":5},"supports":[]}
	 */
    public function AttackPlanet($units, $planetId)
    {
        $data = $units;
        return $this->client->Post("/movement/attack/" . $planetId, $data);
    }
    public function SupportPlanet($units, $planetId)
    {
        $data = $units;
        return $this->client->Post("/movement/support/" . $planetId, $data);
    }
    public function OccupyPlanet($planetId)
    {
        return $this->client->Post("/movement/occupy/" . $planetId, []);
    }

	public function SupportUnit($unitType, $quantity, $planetId)
	{
        $data = [
            "quantity" => [
                $unitType => $quantity
            ]
        ];
		return $this->client->Post("/movement/support/" . $planetId, $data);
	}	
	
	
	public function CompleteExpedition($id)
	{
		return $this->client->Post("/expedition/" . $id, false);
	}		
	public function CompleteMission($id)
	{
		return $this->client->Post("/mission/" . $id, false);
	}



	
	
	public function log($text)
	{
		echo date("[d.m.Y H:i:s]") . " [" . $this->planet_name . "] " . $text . "\n";
	}
	
}
