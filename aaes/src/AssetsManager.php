<?php
class AssetsManager
{
	private static $host;
	private static $rootDir;
	private static $aaesDir;
	private static $srcDir;

	public static function init()
	{
		self::$host    = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/';
		self::$rootDir = $_SERVER['DOCUMENT_ROOT'] . '/';
		self::$aaesDir = self::$rootDir . 'aaes/';
		self::$srcDir  = self::$aaesDir . 'src/';
		
		spl_autoload_register([self, 'loadClass'], true, false);
	}

	private static function loadClass($name)
	{
		$url = self::$srcDir . $name . '.php';

		if (file_exists($url)
		 || file_exists($url = self::getControllerUrl($name))
		 || file_exists($url = self::getModelUrl($name))
		) {
			require_once $url;
		}
	}

	public static function getControllerUrl($name)
	{
		return self::$rootDir . 'controllers/' . $name . '.php';
	}
	
	public static function checkController($name)
	{
		$url = self::getControllerUrl($name);

		return file_exists($url);
	}

	public static function getModelUrl($name)
	{
		return self::$rootDir . 'models/' . $name . '.php';
	}
	
	public static function checkModel($name)
	{
		$url = self::getModelUrl($name);

		return file_exists($url);
	}

	public static function getViewUrl($name)
	{
		$name = str_replace('.', '/', $name);
		
		return self::$rootDir . 'views/' . $name . '.php';
	}
	
	public static function checkView($name)
	{
		$url = self::getViewUrl($name);

		return file_exists($url);
	}

	public static function getAssetUrl($url)
	{
		$fl = true;

		foreach (['//', 'http://', 'https://'] as $needle) {
			if (strpos($url, $needle) === 0) {
				$fl = false;
				break;
			}
		}
		
		if ($fl) {
			$url = self::$host . $url;
		}

		return $url;
	}
}