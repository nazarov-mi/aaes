<?php

class Entity
{
	protected $app;

	final public function init($app)
	{
		$this->app = $app;
	}
}