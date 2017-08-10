<?php

class MainController extends Controller
{

	public function __construct()
	{
		$this->addRoute('seeder', Auth::ALL, 'GET');
		$this->addRoute('index',  Auth::ALL, 'GET');
		$this->addRoute('page',   Auth::ALL, 'GET');
	}

	public function seeder()
	{
		$app = $this->app;

		$app->create('UserModel')->seeder();
		// Other

		return 'База данных успешно обновлена';
	}

	public function index()
	{
		return (new View())->show('home');
	}

	public function page($request, $id)
	{
		$view = 'home';

		if (!empty($id)) {
			$view = 'pages.page' . $id;
		}

		return (new View())->show($view);
	}
}