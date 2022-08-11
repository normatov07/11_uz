<?php defined('SYSPATH') or die('No direct script access.');

class News_Entry_Model extends ORM {

	protected $sorting = array('published' => 'desc', 'added'=>'desc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);

		if(empty($id)):
			$this->added = date::getForDb();
		endif;
	}

	public function __get($column){
	
		switch((string) $column):
			case 'url':
				if (empty($this->object[$column])) {
					$this->object[$column] = '/news/' . $this->id .'/';
				}
				return $this->object[$column];
			break;
		endswitch;
		
		return parent::__get($column);
	}
	
	
	public function countPublished($limit = NULL, $offset = NULL){
		return $this->where(array('status' => 'enabled', 'published <= ' => date::getForDb()))->count_all();
	}

	public function findPublished($limit = NULL, $offset = NULL){
		return $this->where(array('status' => 'enabled', 'published <= ' => date::getForDb()))->find_all($limit, $offset);
	}

}
/* ?> */