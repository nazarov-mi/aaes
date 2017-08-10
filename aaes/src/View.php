<?php
class View extends Response
{
	private $data              = [];
	private $bufferContentType = [];
	private $includedFiles     = [];
	private $includedViews     = [];
	private $currSectionName;
	

	public function __construct()
	{
		parent::__construct();
		$this->removeAll();
	}

	/**
	 * Закрепляет callback функцию за конкретной меткой
	 * @param name - название метки
	 * @param callback - callback функция
	 */
	private function addCallback($name, $callback)
	{
		$this->bufferContentType[$name] = [$this, $callback];
	}

	/**
	 * Заменяет все метки на данные
	 * @param $content
	 */
	private function parseContent($content)
	{
		$pattern = '/\{{(@?[a-z0-9_-]+)\}}/i';

		return preg_replace_callback($pattern, [$this, 'replaceMark'], $content);
	}

	/**
	 * Callback функция для обработки меток
	 * @param data - результат поиска фукции preg_replace_callback
	 * @return Возвращает текст для замены метки
	 */
	private function replaceMark($data)
	{
		$name = $data[1];
		$callback = $this->bufferContentType[$name];
		
		if (isset($callback)) {
			return call_user_func($callback, $name);
		}
		
		return '';
	}
	
	/**
	 * Подключает view
	 * @param $name - имя view
	 */
	public function inc($viewName, array $params = null)
	{
		if (!in_array($viewName, $this->includedViews)) {
			$this->includedViews[] = $viewName;
		}

		if (isset($params)) {
			foreach ($params as $key => $value) {
				${$key} = $value;
			}
			unset($params, $key, $value);
		}

		include AssetsManager::getViewUrl($viewName);
	}
	
	/**
	 * Выводит view со всеми зависимостями и метками
	 * @param $name - имя view
	 */
	public function show($name, array $params = null)
	{
		ob_start();
		$this->inc($name, $params);
		$content = ob_get_clean();
		$content = $this->parseContent($content);

		return $this->setContent($content);
	}


	// Работа с секциями


	/**
	 * Начинает запись секции
	 * @param $name - название секции
	 */
	public function startSection($name)
	{
		$this->currSectionName = $name;

		ob_start([$this, "setSectionData"]);
	}
	
	/**
	 * Заканчивает запись секции
	 */
	public function endSection()
	{
		ob_end_clean();
	}
	
	/**
	 * Устанавливает данные для замены метки
	 * @param content - данные секции для замены
	 */
	private function setSectionData($content)
	{
		$name = '@' . $this->currSectionName;
		
		if (!isset($this->data[$name])) {
			$this->addCallback($name, 'getSectionData');
		}
		
		$this->data[$name] = $content;
	}
	
	/**
	 * Callback
	 * Обрабатывает и возвращает данные секции
	 */
	private function getSectionData($name)
	{
		$content = $this->data[$name];
		
		if (!empty($content)) {
			return $this->parseContent($content);
		}
		
		return '';
	}


	// Работа с метками


	/**
	 * Устанавливает данные для замены метки
	 * @param name - название метки
	 * @param value - данные для замены
	 */
	public function set($name, $value)
	{
		$this->addCallback($name, 'get');
		$this->data[$name] = $value;
	}
	
	/**
	 * Возвращает данные для замены метки
	 * @param name - название метки
	 */
	public function get($name)
	{
		return $this->data[$name];
	}

	/**
	 * Устанавливает данные для замены меток
	 * @param array - массив (название метки => данные для замены)
	 */
	public function setArray($array, $prefix = '')
	{
		if (is_array($array)) {
			foreach ($array as $name => $value) {
				$this->set($prefix . $name, $value);
			}
		}
	}

	/**
	 * Устанавливает заголовок страницы
	 * @param title - заголовок страницы
	 */
	public function setTitle($title)
	{
		$this->set('title', $title);
	}


	// Работа с метками view

	
	/**
	 * Устанавливает путь к файлу для замены метки
	 * @param name - название метки
	 * @param view - адрес файла
	 */
	public function setView($name, $view)
	{
		if (!isset($this->data[$name])) {
			$this->addCallback($name, 'getViewData');
		}
		
		$this->data[$name] = $view;
	}
	
	/**
	 * Callback
	 * Загружает образец по названию метки и обрабатывает все вложенные метки
	 * @param name - название метки
	 * @return Возвращает обработанный код страницы
	 */
	private function getViewData($mark)
	{
		$name = $this->data[$mark];
		$content = $this->inc($name);
		
		if (isset($content)) {
			return $this->parseContent($content);
		}
		
		return '';
	}


	// Работа с файлами и меткой head
	

	/**
	 * Подключает файл
	 * @param name - имя файла
	 * @param src - ссылка на файл (.js, .css)
	 */
	public function addFile($name, $src)
	{
		$src = AssetsManager::getAssetUrl($src);

		if (!isset($this->includedFiles[$name])) {
			$this->includedFiles[$name] = $src;
		}
	}

	/**
	 * Подключает файлы
	 * @param arr - массив (название файла => ссылка на файл (.js, .css))
	 */
	public function addFiles(array $arr)
	{
		foreach ($arr as $name => $src) {
			$this->addFile($name, $src);
		}
	}

	/**
	 * Возвращает ссылку на файл
	 * @param name - имя файла
	 */
	public function getFileSrc($name)
	{
		return $this->includedFiles[$name];
	}

	/**
	 * Отключает файл
	 * @param name - название файла
	 */
	public function removeFile($name)
	{
		unset($this->includedFiles[$name]);
	}

	/**
	 * Отключает файлы
	 * @param arr - массив названий
	 */
	public function removeFiles(array $arr)
	{
		foreach ($arr as $name) {
			$this->removeFile($name);
		}
	}

	/**
	 * Отключает все файлы
	 */
	public function removeAllFiles()
	{
		unset($this->includedFiles);
		$this->includedFiles = [];
	}

	/**
	 * Callback
	 * Возвращает данные head
	 */
	private function getHeadData()
	{
		$headData = '';

		foreach ($this->includedFiles as $src) {
			if (stripos($src, '.js') !== false) {
				$headData .= '<script type="text/javascript" src="' . $src . '"></script>';
			} elseif (stripos($src, '.css') !== false) {
				$headData .= '<link rel="stylesheet" type="text/css" href="' . $src . '" />';
			} else {
				throw new Except('Неизвестный тип файла ' . $src);
			}
		}

		return $headData;
	}

	/**
	 * Удаляет данные для замены метки и callback
	 * @param name - название метки или массив
	 */
	public function remove($name)
	{
		if (is_array($name)) {
			foreach ($name as $item) {
				unset($this->data[$item]);
				unset($this->bufferContentType[$item]);
			}
		} else {
			unset($this->data[$name]);
			unset($this->bufferContentType[$name]);
		}
	}
	
	/**
	 * Удаляет все данные для замены меток и callback'и, кроме head
	 */
	public function removeAll()
	{
		array_splice($this->data, 0);
		array_splice($this->bufferContentType, 0);
		$this->addCallback('head', 'getHeadData');
	}
}