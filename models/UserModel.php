<?php

class UserModel extends Auth
{

	public function __construct($data = null)
	{
		parent::__construct(
			'users',
			[
				'id',
				'name',
				'username',
				'password',
				'status'
			],
			$data
		);
	}

	public function seeder()
	{
		$this->clearTable();

		$data = [
			['name' => 'Администратор', 'username' => 'Admin']
		];

		$url = "http://randus.ru/api.php";
		$context = stream_context_create([
			"http" => [
				"method" => "POST",
				"header" => "Content-type: application/x-www-form-urlencoded"
			]
		]);

		for ($i = 0; $i < 5; ++ $i) {
			$res = json_decode(file_get_contents($url, false, $context));

			$data[] = [
				'name' => $res->lname . ' ' . $res->fname . ' ' . $res->patronymic,
				'username' => $res->login
			];
		}

		foreach ($data as $id => $item) {
			$m = $this->app->create('UserModel', [
				'id'       => $id + 1,
				'name'     => $item['name'],
				'username' => $item['username'],
				'password' => 1,
				'status'   => Auth::ADMIN
			]);
			$m->insert();
		}
	}
}