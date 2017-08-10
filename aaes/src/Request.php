<?php

class Request
{

	protected $query;
	protected $request;
	protected $cookies;
	protected $files;
	protected $server;

	
	public function __construct(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = [])
	{
		$this->query   = new Dictionary($query);
		$this->request = new Dictionary($request);
		$this->cookies = new Dictionary($cookies);
		$this->files   = new Dictionary($files);
		$this->server  = new Dictionary($server);
	}

	public function fromGlobals()
	{
		$request = $_POST;
		$restJson = file_get_contents('php://input');
		$restData = json_decode($restJson, true);
		
		if ($restData) {
			$request = array_replace($request, $restData);
		}

		return new self($_GET, $request, $_COOKIE, $_FILES, $_SERVER);
	}

	
	// GETTERS / SETTERS


	public function getAll()
	{
		return $this->getMethod() == 'GET' ? $this->query : $this->request;
	}

	public function getCookies()
	{
		return $this->cookies;
	}

	public function getFiles()
	{
		return $this->files;
	}

	public function getServer()
	{
		return $this->server;
	}

	public function getMethod()
	{
		return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
	}

	public function __get($key)
	{
		return $this->getAll()->get($key);
	}

	public function __isset($key)
	{
		return $this->getAll()->__isset($key);
	}
}