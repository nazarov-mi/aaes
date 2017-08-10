<?php
class Image
{
	private $tmp_name;
	private $type;
	private $width;
	private $height;
	private $src;

	public function __construct($tmp_name)
	{
		if (empty($tmp_name)) {
			throw new Except('Неверное имя файла');
		}
		
		if ($info = getimagesize($tmp_name)) {
			$this->tmp_name = $tmp_name;
			
			$this->type = trim(strrchr($info['mime'], '/'), '/');
			list($this->width, $this->height) = $info;
			
			$imagecreate = 'imagecreatefrom' . $this->type;
			$this->src = $imagecreate($tmp_name);
		} else {
			throw new Except('Не удалось получить данные файла');
		}
	}

	public function dispose()
	{
		unset($this->src);
	}

	public function save($dir, $name = null, $dispose = true)
	{
		if (empty($this->src)) {
			return false;
		}

		if (empty($name)) {
			$name = md5(time() . rand());
		}
		
		$name .= '.' . $this->type;
		
		if (is_dir($dir) || mkdir($dir, 0777)) {
			$imagesave = 'image' . $this->type;
			$url = $dir . $name;
			$res = $imagesave($this->src, $url);
		}
		
		if ($dispose) {
			$this->dispose();
		}

		return ($res ? $url : false);
	}

	public function getWidth()
	{
		return $width ?: 0;
	}

	public function getHeight()
	{
		return $height ?: 0;
	}

	public function getType()
	{
		return $this->type;
	}

	public function resize($width, $height)
	{
		if (empty($this->src)) {
			return false;
		}

		$dst_img = imagecreatetruecolor($width, $height);

		imagecopyresampled($dst_img, $this->src, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		unset($this->src);

		$this->src = $dst_img;

		return true;
	}

	public function resizeFromEdge($size, $max = true)
	{
		$w = $this->width;
		$h = $this->height;

		if ($w == 0 || $h == 0) {
			return false;
		}

		$wp = ($w > $h) == $max ? $size : ceil(($w * $size) / $h);
		$hp = ($w < $h) == $max ? $size : ceil(($h * $size) / $w);
		
		return $this->resize($wp, $hp);
	}
}