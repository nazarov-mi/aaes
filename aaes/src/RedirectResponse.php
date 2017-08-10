<?php

class RedirectResponse extends Response
{

	public function __construct($url, $code = 302)
	{
		parent::__construct(null, $code);
		$response->setHeader('Location', $url);
	}
}