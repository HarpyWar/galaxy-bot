<?php

namespace GalaxyBot;
use GalaxyBot\Config;

require_once("Auth.php");

class GalaxyClient
{
	private $auth;
	private $api;
	private $headers;
	private $apiUrl = "https://galaxy.bipgame.io/api";
	private $cookies = "";
	
	public function __construct($api)
	{
		$this->api = $api;
		$this->auth = new Auth();
	}
	
	public function Auth($login, $password)
	{
		$this->headers = $this->auth->Login($login, $password);
	}
	
	private function getHeader()
	{
		#$this->headers = "X-CSRF-TOKEN: JfNPbbprDGEEU6OaJKhd5bLTu5qjB99KSwb15qGa\r\nCookie: laravel_token=eyJpdiI6IjE5ZFlZcHVBYlR6OUNnYzJCMDVRYWc9PSIsInZhbHVlIjoiMExBWm9hKzFsR3pJVXA1ZmJrNE5XZnJsKzRkR2htNlhvVFRRcVlBcTI5V05kWHVnXC84UHdmXC81NVAwUGRFNlg5OHYydG9PQ2lpY210cDlZbXJLaFBwN0MwSWF6d2J3RUFhT0VINVZ6cHVqVVorbEhrNWk1NmR5Wnd2anYxV01zcnVVSHpHYmtxeVdWTDdlRVdlQm1yaTN4N1wvWWk2ZzVGQkhZSlNaXC80SEs0cW9XMXpYTUZvSWd0VVJVdW5oQ2ZwN1dtbUFORDkxaCszUjI5aEIyYXdKa2tcL0dWb3ZHZVpUSFR0M1YyclpPQVJDV0VjQUZOejV3d2t0ckdZT0FjNkxuIiwibWFjIjoiMDZhODZlNGEzNTgxOWE3NjFhNmM0YTg4OGUwNzk0MDA5M2U2N2Y1ZGQxZmI1M2MxYzhkNDQyNGMyNzg5MTZlZCJ9; ";

		return "Content-Type: application/json
Connection: keep-alive
Accept: application/json, text/plain, */*
Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7
User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4026.0 Safari/537.36
Accept: application/json
Host: galaxy.bipgame.io
Referer: https://galaxy.bipgame.io/
X-Requested-With: XMLHttpRequest\r\n" .
			   $this->headers;
	}
	
	public function Post($url, $data)
	{
		$options = array(
			'http' => array(
				'method'  => 'POST',
				'content' => json_encode( $data )
			)
		);
		return $this->request($url, $options);
	}
	
	public function Put($url)
	{
		$options = array(
			'http' => array(
				'method'  => 'PUT'
			)
		);
		return $this->request($url, $options);
	}
	
	public function Get($url)
	{
		$options = array(
			'http' => array(
				'method'  => 'GET'
			)
		);
		return $this->request($url, $options);
	}

	
	private function request($url, $options)
	{
		$options['http']['header'] = $this->getHeader();
		$response = "";

		$requestUrl = substr($url, 0, 4) == "http"
			? $url
			: $this->apiUrl . $url;
		$context  = stream_context_create($options);
		$result = file_get_contents($requestUrl, false, $context);
		if (!$result)
		{
			/*
			Array
			(
				[type] => 2
				[message] => file_get_contents(https://galaxy.bipgame.io/api/planet): failed to open stream: HTTP request failed! HTTP/1.1 401 Unauthorized

				[file] => M:\Projects\web\galaxy_bot\GalaxyClient.php
				[line] => 183
			)
			*/
			// on error try login and repeat the same request
			if ($error = error_get_last())
			{
				if (strpos($error['message'], '401 Unauthorized') !== false)
				{
					$this->api->Login();
					#print_r($this->getHeader());
                    error_clear_last();
					return $this->request($url, $options);
				}
				// FIXME: 400 here could be, don't handle it
				// throw new Exception("TODO: unhandled error (empty response?)");
			}
			// should be 200 OK here
		}
		$response = json_decode($result);

		return $response;
	}
}

