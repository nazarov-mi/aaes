<?php
class Except extends Exception
{
	private $statusText;
	private $statusCode;

	public function __construct($text = null, $code = 500)
	{
		$this->statusText = $text;
		$this->statusCode = $code;
	}
	
	public function getStatusText()
	{
		return $this->statusText;
	}
	
	public function getStatusCode()
	{
		return $this->statusCode;
	}
}