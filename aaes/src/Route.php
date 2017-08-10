<?php

class Route
{
	private $name;
	private $status;
	private $method;
	
	public function __construct($name, $status, $method = null)
	{
		$this->name = $name;
		$this->status = $status;
		$this->method = $method;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getMethod()
	{
		return $this->method;
	}
}