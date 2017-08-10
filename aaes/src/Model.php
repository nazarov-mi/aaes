<?php
abstract class Model extends Entity
{
	protected $tablename;
	protected $cols;
	protected $toJson;

	private $data;
	private $hasCreatedAtCol;
	private $hasUpdatedAtCol;

	public function __construct($tablename, $cols, $data = null, $toJson = null)
	{
		if (!is_array($cols) || count($cols) == 0) {
			throw new Except('Аргумент fields должен быть массивом и включать больше 0 элементов');
		}

		$this->tablename = $tablename;
		$this->cols = $cols;
		$this->toJson = (is_array($toJson) ? $toJson : []);

		$this->hasCreatedAtCol = in_array('created_at', $this->cols);
		$this->hasUpdatedAtCol = in_array('updated_at', $this->cols);

		$this->record($data);
	}

	private function updateCreatedAtCol()
	{
		if ($this->hasCreatedAtCol) {
			$this->set('created_at', date('Y-m-d H:i:s'));
		}
	}

	private function updateUpdatedAtCol()
	{
		if ($this->hasUpdatedAtCol) {
			$this->set('updated_at', date('Y-m-d H:i:s'));
		}
	}



	public function clear()
	{
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
	
	public function getAllPrepared()
	{
		$data = $this->getAll();
		
		foreach ($this->toJson as $key) {
			$data[$key] = json_encode($data[$key]);
		}
		
		return $data;
	}

	public function get($key, $default = null)
	{
		return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
	}

	public function set($key, $value)
	{
		if (in_array($key, $this->cols)) {
			$this->data[(string) $key] = $value;
		}
	}

	public function add($data)
	{
		if (empty($data)) return;

		foreach ($data as $key => $val) {
			if (in_array($key, $this->toJson) && is_string($val)) {
				$val = json_decode($val);
			}

			$this->set($key, $val);
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
	


	public function find($id)
	{
		$db = $this->app->getDB();

		$sql = 'SELECT * FROM :n WHERE `id`=:d';
		$tablename = $this->tablename;

		$data = $db->select($sql, $tablename, $id);

		$this->record($data);

		return $this;
	}

	public function insert()
	{
		$this->updateCreatedAtCol();
		$this->updateUpdatedAtCol();

		$db = $this->app->getDB();

		$sql = 'INSERT INTO :n SET :a';
		$tablename = $this->tablename;
		$data = $this->getAllPrepared();

		$id = $db->insert($sql, $tablename, $data);

		if ($id === false) {
			return false;
		}

		$this->set('id', $id);
		
		return true;
	}

	public function update()
	{
		$this->updateUpdatedAtCol();
		
		$db = $this->app->getDB();

		$sql = 'UPDATE :n SET :a WHERE `id`=:d';
		$tablename = $this->tablename;
		$data = $this->getAllPrepared();
		$id = $this->get('id');

		return $db->update($sql, $tablename, $data, $id);
	}

	public function save()
	{
		$id = $this->get('id');

		if (empty($id)) {
			return $this->insert();
		}
		
		return $this->update();
	}

	public function deleteById($id)
	{
		$db = $this->app->getDB();

		$sql = 'DELETE FROM :n WHERE `id`=:d';
		$tablename = $this->tablename;

		return $db->delete($sql, $tablename, $id);
	}

	public function delete()
	{
		$id = $this->get('id');

		return $this->deleteById($id);
	}

	public function getList($page = 0, $num = 1000000, $orderBy = 'id', $toLower = true)
	{
		$db = $this->app->getDB();

		$tablename = $this->tablename;
		$sql = 'SELECT * FROM :n WHERE 1';
		$sql = $db->prepareSql($sql, $tablename);

		return new ArrayList($sql, $page, $num, $orderBy, $toLower);
	}

	public function clearTable()
	{
		$db = $this->app->getDB();
		$tablename = $this->tablename;

		return $db->clearTable($tablename);
	}
}