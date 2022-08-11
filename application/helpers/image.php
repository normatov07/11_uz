<?php 

class image_Core{
	
	/**
	 * Draw a watermark over an image (the watermark position is
	 * selected automatically) and returns true. If the watermark
	 * is bigger than the image, this method returns false.
	 *
	 * @param IMagick $image
	 * @param IMagick $watermark
	 * @param int $padding
	 * @return bool
	 */
		
	public function addWatermark($image, $watermark = FALSE, $hpadding = 0, $vpadding = 0){
		
		if($watermark == FALSE and Lib::config('app.watermark_file') != NULL) $watermark = new Imagick(Lib::config('app.watermark_file'));
		elseif(is_string($watermark)) $watermark = new Imagick($watermark);
		else return false;
		
		$image_width 		= $image->getImageWidth();
		$image_height 		= $image->getImageHeight();
		$watermark_width 	= $watermark->getImageWidth();
		$watermark_height 	= $watermark->getImageHeight();
	
		if ($image_width < $watermark_width + $hpadding || $image_height < $watermark_height + $vpadding) {
			return false;
		}
		
		$width = $image_width - $watermark_width - $hpadding;
		$height = $image_height - $watermark_height - $vpadding;
		$image->compositeImage($watermark, $watermark->getImageCompose(), $width, $height, imagick::COLOR_ALPHA);

		return true;
	}
	
	public function addSmartWatermark($image, $watermark, $padding = 0)
	{
		// Check if the watermark is bigger than the image
		$image_width 		= $image->getImageWidth();
		$image_height 		= $image->getImageHeight();
		$watermark_width 	= $watermark->getImageWidth();
		$watermark_height 	= $watermark->getImageHeight();
	
		if ($image_width < $watermark_width + $padding || $image_height < $watermark_height + $padding) {
			return false;
		}
	
		// Calculate each position
		$positions = array();
		$positions[] = array(0 + $padding, 0 + $padding);
		$positions[] = array($image_width - $watermark_width - $padding, 0 + $padding);
		$positions[] = array($image_width - $watermark_width - $padding, $image_height - $watermark_height - $padding);
		$positions[] = array(0 + $padding, $image_height - $watermark_height - $padding);
	
		// Initialization
		$min = null;
		$min_colors = 0;
	
		// Calculate the number of colors inside each region
		// and retrieve the minimum
		foreach($positions as $position)
		{
			$colors = $image->getImageRegion(
				$watermark_width,
				$watermark_height,
				$position[0],
				$position[1])->getImageColors();
	
			if ($min === null || $colors <= $min_colors)
			{
				$min 		= $position;
				$min_colors = $colors;
			}
		}
	
		// Draw the watermark
		$image->compositeImage(
			$watermark,
			Imagick::COMPOSITE_OVER,
			$min[0],
			$min[1]);
	
		return true;
	}
	

	
}
/* ?> */