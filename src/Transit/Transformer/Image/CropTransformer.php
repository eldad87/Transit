<?php
/**
 * @copyright	Copyright 2006-2013, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/transit
 */

namespace Transit\Transformer\Image;

use Transit\File;
use \InvalidArgumentException;

/**
 * Crops a photo, but resizes and keeps aspect ratio depending on which side is larger.
 */
class CropTransformer extends AbstractImageTransformer {

	const TOP = 'top';
	const BOTTOM = 'bottom';
	const LEFT = 'left';
	const RIGHT = 'right';
	const CENTER = 'center';

	/**
	 * Configuration.
	 *
	 * @var array
	 */
	protected $_config = array(
		'location' => self::CENTER,
		'quality' => 100,
		'width' => null,
		'height' => null
	);

	/**
	 * Calculate the transformation options and process.
	 *
	 * @param \Transit\File $file
	 * @param bool $self
	 * @return \Transit\File
	 * @throws \InvalidArgumentException
	 */
	public function transform(File $file, $self = false) {
		$config = $this->_config;
		$baseWidth = $file->width();
		$baseHeight = $file->height();
		$width = $config['width'];
		$height = $config['height'];

		if (is_numeric($width) && !$height) {
			$height = round(($baseHeight / $baseWidth) * $width);

		} else if (is_numeric($height) && !$width) {
			$width = round(($baseWidth / $baseHeight) * $height);

		} else if (!is_numeric($height) && !is_numeric($width)) {
			throw new InvalidArgumentException('Invalid width and height for crop');
		}

		$location = $config['location'];
		$widthScale = $baseWidth / $width;
		$heightScale = $baseHeight / $height;
		$src_x = 0;
		$src_y = 0;
		$src_w = $baseWidth;
		$src_h = $baseHeight;

		// Source width is larger, use height scale as the base
		if ($widthScale > $heightScale) {
			$src_w = round($width * $heightScale);

			// Position horizontally in the middle
			if ($location === self::CENTER) {
				$src_x = round(($baseWidth / 2) - (($width / 2) * $heightScale));

			// Position at the far right
			} else if ($location === self::RIGHT || $location === self::BOTTOM) {
				$src_x = $baseWidth - $src_w;
			}

		// Source height is larger, use width scale as the base
		} else {
			$src_h = round($height * $widthScale);

			// Position vertically in the middle
			if ($location === self::CENTER) {
				$src_y = round(($baseHeight / 2) - (($height / 2) * $widthScale));

			// Position at the bottom
			} else if ($location === self::RIGHT || $location === self::BOTTOM) {
				$src_y = $baseHeight - $src_h;
			}
		}

		return $this->_process($file, array(
			'dest_w'	=> $width,
			'dest_h'	=> $height,
			'source_x'	=> $src_x,
			'source_y'	=> $src_y,
			'source_w'	=> $src_w,
			'source_h'	=> $src_h,
			'quality'	=> $config['quality'],
			'overwrite'	=> $self
		));
	}

}