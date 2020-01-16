<?php

namespace GalaxyBot;

class Auth
{
	private $cookiePath = 'cookies';

	public function __construct()
	{
		
	}
	
	public function Login($login, $password)
	{
		echo "log in for $login ...\n";
		$cookieFile = $this->cookieFile($login);


		$ch = curl_init();
		$url = 'https://bipgame.io/public/users/login?online&domain=galaxy.bipgame.io';
		$options = array(CURLOPT_URL => $url,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => "purse=".$login."&password=".$password,
						CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_COOKIEFILE => $cookieFile,
						CURLOPT_COOKIEJAR => $cookieFile,
						CURLOPT_RETURNTRANSFER => true
						);

		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		curl_close($ch);

		$cookies = $this->extractCookies(file_get_contents($cookieFile));
		$session_id = $cookies['PHPSESSID_production']['value'];
		
		
		#print_r($session_id);
		#echo "\n";
		#print_r($xsrf_token);
		#echo "\n";

		$ch = curl_init();
		$url = 'https://galaxy.bipgame.io/login?h=' . $session_id;
		$options = array(CURLOPT_URL => $url,
						CURLOPT_FOLLOWLOCATION => 1,
						CURLOPT_COOKIEFILE => $cookieFile,
						CURLOPT_COOKIEJAR => $cookieFile,
						CURLOPT_RETURNTRANSFER => 1
						);

		curl_setopt_array($ch, $options);
		$result =  curl_exec($ch);
		curl_close($ch);

		preg_match('/<meta\s+name=\"csrf-token\"\s+content=\"([^"]+)\"/', $result, $matches);
		if (count($matches) != 2)
			throw new Exception("Can't find csrf-token");

		$csrf_token = $matches[1];
		#print_r($csrf_token);
		#die();
		
		
		$cookies = $this->extractCookies(file_get_contents($cookieFile));
		$xsrf_token = $cookies['XSRF-TOKEN']['value'];
		$laravel_token = $cookies['laravel_token']['value'];
		$galaxy_of_drones_online_session = $cookies['galaxy_of_drones_online_session']['value'];
		

		$header_str = "X-CSRF-TOKEN: {$csrf_token}\r\n" . // required
					  "X-XSRF-TOKEN: {$xsrf_token}\r\n" . // not required?
					  #"Cookie: laravel_token={$laravel_token};"; // required
					  "Cookie: PHPSESSID_production={$session_id}; laravel_token={$laravel_token}; galaxy_of_drones_online_session={$galaxy_of_drones_online_session}; XSRF-TOKEN={$xsrf_token}\r\n"; // not required?
		return $header_str;
	}
	
	
	private function cookieFile($login)
	{
		$filename = $this->cookiePath . '/' . $login . '.txt';
		if ( !file_exists($filename) )
		{
			file_put_contents($filename, "");
		}
		return $filename;
	}
	
	/**
	 * Extract any cookies found from the cookie file. This function expects to get
	 * a string containing the contents of the cookie file which it will then
	 * attempt to extract and return any cookies found within.
	 *
	 * @param string $string The contents of the cookie file.
	 * 
	 * @return array The array of cookies as extracted from the string.
	 *
	 */
	private function extractCookies($string)
	{
		$cookies = [];
		$lines = explode(PHP_EOL, $string);

		foreach ($lines as $line) {

			$cookie = array();

			// detect httponly cookies and remove #HttpOnly prefix
			if (substr($line, 0, 10) == '#HttpOnly_') {
				$line = substr($line, 10);
				$cookie['httponly'] = true;
			} else {
				$cookie['httponly'] = false;
			} 

			// we only care for valid cookie def lines
			if( strlen( $line ) > 0 && $line[0] != '#' && substr_count($line, "\t") == 6) {

				// get tokens in an array
				$tokens = explode("\t", $line);

				// trim the tokens
				$tokens = array_map('trim', $tokens);

				// Extract the data
				$cookie['domain'] = $tokens[0]; // The domain that created AND can read the variable.
				$cookie['flag'] = $tokens[1];   // A TRUE/FALSE value indicating if all machines within a given domain can access the variable. 
				$cookie['path'] = $tokens[2];   // The path within the domain that the variable is valid for.
				$cookie['secure'] = $tokens[3]; // A TRUE/FALSE value indicating if a secure connection with the domain is needed to access the variable.

				$cookie['expiration-epoch'] = $tokens[4];  // The UNIX time that the variable will expire on.   
				$cookie['name'] = urldecode($tokens[5]);   // The name of the variable.
				$cookie['value'] = urldecode($tokens[6]);  // The value of the variable.

				// Convert date to a readable format
				$cookie['expiration'] = date('Y-m-d h:i:s', $tokens[4]);

				// Record the cookie.
				$cookies[$cookie['name']] = $cookie;
			}
		}

		return $cookies;
	}
	
	
	
}