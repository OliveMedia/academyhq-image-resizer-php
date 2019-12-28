<?php

namespace Suin\ImageResizer;

use \RuntimeException;

class ImageResizerWithoutCropping extends ImageResizer {

	public function __construct($filename) {

		parent::__construct($filename);
	}

	public function resize()
	{
		if ( $this->_needsResize() == false )
		{
			return true;
		}

		$newSize = $this->_calculateNewSizeByMaxSize();

		// TODO >> Consider open closed principle
		switch ( $this->type )
		{
			case IMAGETYPE_JPEG:
				$source = imagecreatefromjpeg($this->filename);
				break;
			case IMAGETYPE_GIF:
				$source = imagecreatefromgif($this->filename);
				break;
			case IMAGETYPE_PNG:
				$source = imagecreatefrompng($this->filename);
				break;
			default:
				throw new RuntimeException(sprintf("Not supported type of image: %s", $this->filename));
		}

		$canvas = imagecreatetruecolor($this->maxWidth, $this->maxHeight); // Requires GD 2.0.28 or later

		// Check if this image is PNG or GIF, then set if Transparent
		if ( $this->type == IMAGETYPE_PNG or $this->type == IMAGETYPE_GIF )
		{
			imagealphablending($canvas, false);
			imagesavealpha($canvas, true);
			$transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
			imagefilledrectangle($canvas, 0, 0, $newSize['width'], $newSize['height'], $transparent);
		}

		if ( imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newSize['width'], $newSize['height'], $this->originalWidth, $this->originalHeight) == false )
		{
			return false;
		}

		// TODO >> Consider open closed principle
		switch ( $this->type )
		{
			case IMAGETYPE_JPEG:
				imagejpeg($canvas, $this->filename);
				break;
			case IMAGETYPE_GIF:
				imagegif($canvas, $this->filename);
				break;
			case IMAGETYPE_PNG:
				imagepng($canvas, $this->filename);
				break;
			default:
				throw new RuntimeException(sprintf("Not supported type of image: %s", $this->filename));
		}

		return true;
	}

	protected function _calculateNewSizeByMaxSize()
	{

		$width_scale = $this->maxWidth / $this->originalWidth;
		$height_scale = $this->maxHeight / $this->originalHeight;

		if($width_scale > $height_scale) {

			if($width_scale <= 1) {
				$scaled_width = $this->maxWidth;
				$scaled_height = $this->maxHeight;
			} else {

				$scaled_width = $this->originalWidth;
				$scaled_height = $this->maxHeight;
			}

		} elseif($height_scale > $width_scale) {

			if($height_scale <= 1) {
				$scaled_width = $this->maxWidth;
				$scaled_height = $this->maxHeight;
			} else {

				$scaled_height = $this->originalHeight;
				$scaled_width = $this->maxWidth;
			}

		} elseif($width_scale == $height_scale) {

			$scaled_height = $this->maxHeight;
			$scaled_width = $this->maxWidth;
		}

		return array(
			'width'  => $scaled_width,
			'height' => $scaled_height,
		);
	}
}