<?php

class Cortex
{
	public $size;
	public $layers;

	public $resultPath;

	public function __construct($size)
	{
		$this->size = $size;

		// todo: move to class
		$this->resultPath = realpath(__DIR__ . '/../storage') . '/last';

		if (!is_dir($this->resultPath)) {
			if (@mkdir($this->resultPath)) {
				Env::trace("create path '" . $this->resultPath);
			} else {
				Env::warning("can't create path '" . $this->resultPath);
			}
		}
	}

	public function addLayer($name, $layer)
	{
		$this->layers[$name] = $layer;
	}

	public function appendLayer($name, $layer)
	{
		if (!isset($this->layers[$name])) {
			$this->layers[$name] = [];
		}

		$this->layers[$name] = array_merge($this->layers[$name], $layer);
	}

	public function dump($title = null)
	{
		list($width, $height) = $this->size;

		$im = imagecreate($width, $height);
		$bgColor = imagecolorallocate($im, 0, 0, 0);
		$spColor = imagecolorallocate($im, 230, 50, 50); // special

		foreach ($this->layers as $name => $layer) {
			$nameHash = crc32($name);
			$rColor = $gColor = $bColor = 255;
			$rColor -= $nameHash % 255;
			$nameHash = floor($nameHash / 255);
			$gColor -= $nameHash % 255;
			$nameHash = floor($nameHash / 255);
			$bColor -= $nameHash % 255;

			//$fgColor = imagecolorallocate($im, $rColor, $gColor, $bColor);
			$fgColor = imagecolorallocate($im, 50 + rand(0,8)*25, 50 + rand(0,8)*25, 50 + rand(0,8)*25);

			foreach ($layer as list($x, $y)) {
				if ($name != 'neurons') {
					imagesetpixel($im, $x, $y, $fgColor);
				} else {
					imagefilledellipse($im, $x, $y, 3, 3, $fgColor);
				}
			}
		}

		//$im = imagescale($im, 1800, -1, IMG_BICUBIC);

		if (isset($title)) {
			$title = 'dump-layer-' . $title;
		} else {
			$title = 'dump-layer';
		}

		$imagePath = $this->resultPath . '/' . $title . '.png';

		imagepng($im, $imagePath);
		imagedestroy($im);
	}
}