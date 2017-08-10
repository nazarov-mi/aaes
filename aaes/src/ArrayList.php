<?php
class ArrayList implements Iterator
{
	protected $sql;
	protected $num;
	protected $orderBy;
	protected $dirToSort;
	protected $class;

	protected $items = array();
	protected $size = 0;
	protected $page = 0;
	protected $maxSize = 0;
	protected $numPages = 0;
	protected $position = 0;

	public function __construct($sql, $page = 0, $num = 1000000, $orderBy = "id", $toLower = true)
	{
		$db = App::gi()->db;

		$this->sql = $sql;
		$this->setPage($page);
		$this->setNumOfSample($num);
		$this->setFieldToSort($orderBy);
		$this->setDirToSort($toLower ? "DESC" : "ASC");

		$sql = "SELECT COUNT(*) as cnt FROM ($sql) cntr";
		$row = $db->select($sql);

		if ($row && $row->cnt > 0) {
			$this->maxSize  = $row->cnt;
			$this->numPages = (int)(($this->maxSize - 1) / $num) + 1;
		}
	}

	/**
	 * Проверяет пустой массив или нет
	 * @return boolean
	 */
	public function isEmpty()
	{
		return $this->size == 0;
	}

	/**
	 * Проверяет есть ли следующая страница
	 * @return boolean
	 */
	public function hasNext()
	{
		return ($this->page + 1 < $this->numPages);
	}

	/**
	 * Проверяет есть ли предыдущая страница
	 * @return boolean
	 */
	public function hasPrev()
	{
		return ($this->page - 1 >= 0);
	}

	public function calc()
	{
		if ($this->page < 0 || $this->page >= $this->numPages) return false;

		$db = App::getDB();
		$start = $this->page * $this->num;
		$sql = "ORDER BY :n $this->dirToSort LIMIT :d, :d";
		$sql = $this->sql . $db->prepareSql($sql, [$this->orderBy, $start, $this->num]);
		$rows = $db->select($sql, null, true, false);

		if ($rows) {
			array_splice($this->items, 0);

			foreach ($rows as $row) {
				$this->items[] = new $this->class($row);
			}

			$this->size = count($this->items);
			$this->position = 0;

			return true;
		}

		return false;
	}

	// GETTERS / SETTERS
	
	/**
	 * Возвращает колличество элементов в выборке
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}
	
	/**
	 * Возвращает колличество элементов
	 * @return int
	 */
	public function getMaxSize()
	{
		return $this->maxSize;
	}

	/**
	 * Возвращает колличество страниц
	 * @return int
	 */
	public function numPages()
	{
		return $this->numPages;
	}

	
	/**
	 * Возвращает номер текущей сраницы
	 * @return int
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * Загружает страницу
	 * @param page - номер страницы
	 * @return Если страница загружена возвращает TRUE, иначе - FALSE
	 */
	public function setPage($page)
	{
		$this->page = $page;
	}

	/**
	 * Возвращает колличество элементов на страние
	 * @return int
	 */

	public function getNumOfSample()
	{
		return $this->num;
	}

	/**
	 * Устанавливает колличество элементов на страние
	 * @param num - колличество элементов на странице
	 * @return Если страница загружена возвращает TRUE, иначе - FALSE
	 */
	public function setNumOfSample($num)
	{
		$this->num = $num;
	}

	/**
	 * Устанавливает поле для сортировки элементов
	 * @return string
	 */
	public function getFieldToSort()
	{
		return $this->orderBy;
	}

	/**
	 * Устанавливает поле для сортировки элементов
	 * @param orderBy - поле для сортировки
	 * @return Если страница загружена возвращает TRUE, иначе - FALSE
	 */
	public function setFieldToSort($orderBy)
	{
		$this->orderBy = $orderBy;
	}

	/**
	 * Возвращает направление сортировки элементов
	 * @return string
	 */
	public function getDirToSort()
	{
		return $this->dirToSort;
	}

	/**
	 * Устанавливает направление сортировки элементов
	 * @param toLower - направление сортировки
	 * @return Если страница загружена возвращает TRUE, иначе - FALSE
	 */
	public function setDirToSort($dirToSort)
	{
		$this->dirToSort = (strtoupper($dirToSort) === "DESC" ? "DESC" : "ASC");
	}

	/**
	 * Возврвщает код для переключения станиц
	 * @param url - ссылка на страницу
	 * @param string
	 */
	public function getPageSelector($url)
	{
		$url .= (strrpos( $url, "?" ) === false ? "?" : "&") . "page=";
		$from = $this->page *  $this->num + 1;
		$to = $from + $this->size - 1;
		$next = $this->page + 1;
		$prev = $this->page - 1;
		$result = "Записи с $from по $to из $this->maxSize записей<br/>";

		if ($prev >= 0) {
			$result .= "<a href=\"$url$prev\">Предыдущая</a>&nbsp;&nbsp;";
		}

		$result .= "[$next страница]";

		if ($next < $this->numPages) {
			$result .= "&nbsp;&nbsp;<a href=\"$url$next\">Следующая</a>";
		}
		
		return $result;
	}
}