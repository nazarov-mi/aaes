<?php

class Controller extends Entity
{
	private $routes = [];


	final protected function addRoute($name, $status, $method = null)
	{
		$this->routes[$name] = new Route($name, $status, $method);
	}

	final public function _call($name, Request $request, array $params = [])
	{
		$route = $this->routes[$name];

		if (!method_exists($this, $name)
		 || !isset($route)
		 || (
				$route->getMethod() !== null
			 && $route->getMethod() !== $request->getMethod()
		 	)
		) {
			throw new Except('Метод ' . $name . ' не найден в контроллере', 404);
		}

		$user = $this->app->getUser();
		$status = $route->getStatus();

		if (!$user->checkStatus($status)) {
			throw new Except('У пользователя не хватает привелегий', 401);
		}

		$paramArr = array_merge([$request], $params);

		return call_user_func_array([$this, $name], $paramArr);
	}
}