<?php

abstract class Auth extends Model
{
	const GUEST     = 0x1;
	const OPERATOR  = 0x2;
	const MODERATOR = 0x4;
	const ADMIN     = 0x8;
	const AUTHED    = 0xe;
	const ALL       = 0xf;


	public function __construct($tablename, $cols, $data = null, $toJson = null)
	{
		parent::__construct($tablename, $cols, $data, $toJson);

		foreach (['id', 'username', 'password', 'status'] as $name) {
			if (!in_array($name, $cols)) {
				throw new Except('Модель должна содержать поле ' . $name);
			}
		}
	}

	private function makePassword($password)
	{
		$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

		if ($hash === false) {
			throw new Except('Не удалось сгенерировать пароль');
		}

		return $hash;
	}

	private function checkPassword($password, $hash)
	{
		if (strlen($hash) === 0) {
			return false;
		}

		return password_verify($password, $hash);
	}

	public function set($name, $value)
	{
		if ($name === 'password') {
			$value = $this->makePassword($value);
		}

		parent::set($name, $value);
	}

	/**
	 * Проверяет статус пользователя
	 * @param status - статус для проверки
	 * @return true/false
	 */
	public function checkStatus($status)
	{
		return (($this->status & $status) > 0);
	}

	public function loginFromCookie()
	{
		$id = $_COOKIE['USERID'];

		$this->find($id);

		if (empty($this->id)) {
			$this->status = Auth::GUEST;
		}

		return $this;
	}
	
	/**
	 * Проверяет данные пользователя
	 * @param username - логин пользователя
	 * @param password - пароль пользователя
	 * @return Возвращает User или false 
	 */
	public function login($username, $password)
	{
		$db = $this->app->getDB();

		$sql = 'SELECT * FROM :n WHERE `username`=:s';
		$tablename = self::getTableName();

		$data = $db->get($sql, $tablename, $username);
		
		if ($data && $this->checkPassword($password, $data->password)) {
			$this->record($data);
			$this->clearField('password');

			setcookie('USERID', $this->id, 0, '/');

			return true;
		}

		return false;
	}

	public function logout()
	{
		setcookie('USERID', $this->id, time() - 3600, '/');

		$this->clear();
		$this->status = Auth::GUEST;
	}
}