<?php

final class App
{
	private $config;
	private $db;
	private $systemHandlers;
	private $user;


	public function __construct($config)
	{
		$this->config = new Dictionary($config);

		// Система обработки ошибок

		$displayErrors = $this->getSetting('display_errors', false);

		$this->systemHandlers = new SystemHandlers($displayErrors);

		// Экземпляр базы данных

		$this->db = new DB(
			$this->config->get('host', 'localhost'),
			$this->config->get('name', 'test_db'),
			$this->config->get('username', 'root'),
			$this->config->get('password', '')
		);

		// Экземпляр пользователя

		$userModel = $this->getSetting('user_model', 'UserModel');

		if (!AssetsManager::checkModel($userModel)) {
			throw new Except('Модель пользователя с именем ' . $userModel . ' не найдена');
		}

		if (!is_subclass_of($userModel, 'Auth')) {
			throw new Except('Модель пользователя ' . $userModel . ' должна быть наследником класса Auth');
		}
		
		$this->user = $this->create($userModel);
		$this->user->loginFromCookie();

		// Запускаем обработчик URL

		$this->startRouting();
	}

	private function startRouting()
	{
		$route = $_GET['route'];
		$controllerName = 'Main';
		$methodName = 'index';
		
		if (!empty($route)) {
			$params = explode('/', $route);
			
			if (count($params) > 0) {
				$controllerName = array_shift($params);
				$controllerName = ucfirst(strtolower($controllerName));
				
				if (count($params) > 0) {
					$methodName = array_shift($params);
				}
			}
		}

		$controllerName .= 'Controller';
		
		if (!AssetsManager::checkController($controllerName)) {
			throw new Except('контроллер ' . $controllerName . ' не найден');
		}

		$controller = $this->create($controllerName);
		
		$request = Request::fromGlobals();
		$response = $controller->_call($methodName, $request, $params);
		
		if (!($response instanceof Response)) {
			$response = new Response($response);
		}

		$response->send();
	}

	public function create()
	{
		$class = func_get_arg(0);
		$args = func_get_args();
		unset($args[0]);

		if (!isset($class)) {
			throw new Except('Укажите название класса для создания экземпляря');
		}

		if (!is_subclass_of($class, 'Entity')) {
			throw new Except('Класс ' . $class . ' должен быть наследником класса Entity');
		}

		if (count($args) > 0) {
			$args = array_values($args);
			$e = (new ReflectionClass($class))->newInstanceArgs($args);
		} else {
			$e = new $class();
		}

		$e->init($this);

		return $e;
	}


	// GETTERS / SETTERS


	public function getSetting($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	public function getDB()
	{
		return $this->db;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getSystemHandlers()
	{
		return $this->systemHandlers;
	}
}