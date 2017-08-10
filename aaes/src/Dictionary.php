<?php

class Dictionary
{
	private $data = [];

	public function __construct(array $data = null)
	{
		$this->add($data);
	}

	public function clear()
	{
		unset($this->data);
		$this->data = [];
	}

	public function clearField($key)
	{
		unset($this->data[$key]);
	}

	public function has($key)
	{
		return array_key_exists($key, $this->data);
	}

	public function getAll()
	{
		return array_slice($this->data, 0);
	}

	public function get($key, $default = null)
	{
		return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
	}

	public function set($key, $val)
	{
		$this->data[(string) $key] = $val;

		return $val;
	}

	public function add($data)
	{
		if (empty($data)) return;

		foreach ($data as $key => $val) {
			$this->data[$key] = $val;
		}
	}

	public function record($data)
	{
		$this->clear();
		$this->add($data);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __set($key, $val)
	{
		return $this->set($key, $val);
	}

	public function __isset($key)
	{
		return isset($this->data[$key]);
	}

	public function __unset($key)
	{
		$this->clearField($key);
	}
}