<?php


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
		$this->client->Auth($this->account["address"], $this->account["password"]);
	}
	
	public function ChangePlanet($planetId)
	{
		return $this->client->Put("/user/current/" . $planetId);
	}

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
	
	
	public function GetExpeditions()
	{
		return $this->client->Get("/expedition");
	}
	public function GetMissions()
	{
		return $this->client->Get("/mission");
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
	
	// Construct building
	public function Construct($buildingType, $gridId)
	{
		return $this->client->Post("/construction/" . $gridId . "/" . $buildingType, false);
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
