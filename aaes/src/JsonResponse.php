<?php

class JsonResponse extends Response
{

	public function __construct($data, $status = 200, $text = null, $charset = 'UTF-8')
	{
		parent::__construct(json_encode($data), $status, $text, $charset);

		$this->setHeader('Content-Type', 'application/json');
	}
}