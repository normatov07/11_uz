<?php

class Picture_Model extends ORM {

	protected $belongs_to = array('offer');	
	
	protected $sorting = array('priority' => 'asc', 'id' => 'asc');
		
//	protected $has_priority = true;
	
	public $path;
	public $url;
	
	public $new_file;
	
	public $mode;
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);		
		
		if(empty($id)):
			$this->added = date::getForDb();
		endif;
	}

	private function mode(){
		if(empty($this->mode))
			if($this->offer_id):
				$this->mode = 'offer';
			elseif($this->ro_id):
				$this->mode = 'ro';
			else:
				$this->mode = 'default';
			endif;
		return $this->mode;
	}

	private function config($key, $subkey = NULL){
		if($subkey):
			$config = Lib::config('picture.'.$this->mode(), $key);
			return $config[$subkey];
		endif;
		return Lib::config('picture.'.$this->mode(), $key);
	}	

	public function path($type = NULL){
	
		if($type == NULL) $type = 'url';

		$datemis = new date();
		
		if (empty($this->$type)) {
			$folder = $this->config('folder');
			$this->$type = $folder[$this->folder_id][$type] . $datemis->getForURL($this->added,'year-month') . '/';
		}
		
		return $this->$type;
	}
		
	public function f($formatname = 'full', $mode = NULL){
		$format = $this->config('format', $formatname);
		
		if($this->id == 0) return;
		if($mode == 'html'):
			//return '<img src="'.$this->path(NULL) . $this->id . $format['suffix'] . '.jpg'.'" alt="'.$this->title.'"'.($this->{'width' . $format['suffix']} ? ' width="'.$this->{'width' . $format['suffix']}.'"':'').($this->{'height' . $format['suffix']}?' height="'.$this->{'height' . $format['suffix']}.'"':'').' border="0" />';
			return '<img src="'.$this->path(NULL) . $this->id . $format['suffix'] . '.jpg'.'" />';
		else:
			return $this->path($mode) . $this->id . $format['suffix'].'.jpg';
		endif;
	}

	
	public function setFolder(){
	
		foreach($this->config('folder') as $folder_id => $folder):

			if(!empty($folder['status'])):
				$this->folder_id = $folder_id;
				break;
			endif;
			
		endforeach;
		
		if(empty($this->folder_id)) return false;
				
		$path = $this->path('path');
	
		if(WEB_ROOT == substr($path, 0, strlen(WEB_ROOT))):
		
			$fullpath = WEB_ROOT;
			$path = substr($path, strlen(WEB_ROOT));
			
		else:
		
			$fullpath = '';
			
		endif;
			
		$pathParts = explode('/',$path);
		
		foreach($pathParts as $part):
			if(empty($part)) continue;
			$fullpath .= $part.'/';
			
			if(!@file_exists($fullpath)):
				if(!@mkdir($fullpath, 0775)) return false;
				@chmod($fullpath, 0775);
			endif;
		endforeach;

		return true;
	}

	public function save(){
	
		if(!empty($this->new_file)):
		
			if(!file_exists($this->new_file)) return false;
			
			@chmod($this->new_file, 0775);
			
			if(empty($this->folder_id) and !$this->setFolder()) return false;
			
			if($id = parent::save()):
				
				$image = new Imagick($this->new_file);
				$image->stripImage();
			
				$error = false;
				$imageWidth = $image->getImageWidth();
				$imageHeight = $image->getImageHeight();
				
				$image->setCompression(Imagick::COMPRESSION_JPEG);
				//$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
				
				foreach($this->config('format') as $formatname => $format):
					$image->setImageCompressionQuality($format['compression']);
					
					if($format['width'] < $imageWidth or $format['height'] < $imageHeight):
					
						switch($format['method']):
							case 'crop':
								$image->cropThumbnailImage($format['width'], $format['height']);
							break;	
							default:		
									
								$width = $format['width'];
								$height = $format['height'];
								
								if($imageWidth > $imageHeight):

									if(($height = round($format['width']*$imageHeight/$imageWidth)) > $format['height']):
										$height = $format['height'];
										$width = round($format['height']*$imageWidth/$imageHeight);
									endif;									
									
								elseif($imageWidth <= $imageHeight):
								
									if(($width = round($format['height']*$imageWidth/$imageHeight)) > $format['width']):
										$width = $format['width'];
										$height = round($format['width']*$imageHeight/$imageWidth);
									endif;	
									
								endif;		

								$image->resizeImage($width, $height, imagick::FILTER_LANCZOS , $format['blur'], false);
								
							break;
						endswitch;					

					endif;
					
				
									
/**
 * WATERMARK
 */
					if(!empty($format['watermark'])):
					
						$imagetosave = $image->clone();
						if(!file_exists($format['watermark'])) $format['watermark'] = NULL;
						image::addWatermark($imagetosave, $format['watermark']);
						
					else:
					
						$imagetosave = $image->clone();
						
					endif;	


/**
 * PICTURE SIZE
 */
		
					$geometry = $imagetosave->getImageGeometry();
								
/**
 * THUMBNAILING
 */					
					if(!empty($format['composeWithImage']) and file_exists($format['composeWithImage'])):
						
						if($format['width'] != $geometry['width'] or $format['height'] != $geometry['height']){
						
							$canvas = new Imagick($format['composeWithImage']);
							
							$canvas->cropThumbnailImage($format['width'], $format['height']);
							
							$x = 0;
							$y = 0;
							
							// The overlay x and y coordinates 
							$x = ( $format['width'] - $geometry['width'] ) / 2;
							$y = ( $format['height'] - $geometry['height'] ) / 2;
							
	//						echo 'Position: ' . $x . 'x' . $y . '<br>';						exit;
							
							$canvas->compositeImage($imagetosave, imagick::COMPOSITE_OVER, $x, $y);
							
							$imagetosave->destroy();
							
							$imagetosave = &$canvas;
						}
						
						$width = $format['width'];
						$height = $format['height'];
						
					else:
					
						$width = $geometry['width'];
						$height = $geometry['height'];
						
					endif;
					
					if(!$imagetosave->writeImage($this->f($formatname, 'path'))):
					
						$error = true;
						
					endif;
					
					$imagetosave->destroy();					

					if(!file_exists($this->f($formatname, 'path'))):
						$error = true;
					else:
						@chmod($this->f($formatname, 'path'), 0775);
					endif;
					
					$this->{'width' . $format['suffix']} = $width;
					$this->{'height' . $format['suffix']} = $height;
					
				endforeach;
//				exit;
				@unlink($this->new_file);
				
				$image->destroy();				
							
				if($error):
					$this->delete();					
					return false;
				else:					
					return parent::save();
				endif;
					
			endif;
		else:
			return parent::save();	
		endif;
		
	}

	public function delete(){
		@unlink($this->f('full','path'));
		@unlink($this->f('mid','path'));
		@unlink($this->f('thumb','path'));
		
		parent::delete();		
	}
	
	public function find_all_for($mode, $ids){
	
		$this->in($mode.'_id', $ids);
		$this->where('priority','0');
		$list = $this->find_all();

		$array = array();
		
		foreach($list as $item):
			if(empty($array[$item->{$mode.'_id'}]))
			$array[$item->{$mode.'_id'}] = $item;
		endforeach;
		
		return $array;
		
	}

}
/* ?> */