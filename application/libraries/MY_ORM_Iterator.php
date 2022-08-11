<?php defined('SYSPATH') or die('No direct script access.');

class ORM_Iterator extends ORM_Iterator_Core {
	
/**
 * returns Array with ID as an array keys
 */
	
	public function as_id_array($key = NULL)
	{
		$array = array();
		if($key == NULL) $key = $this->primary_key;
		
		if ($results = $this->result->result_array())
		{
			// Import class name
			$class = $this->class_name;

			foreach ($results as $obj)
			{
				$array[$obj->{$key}] = new $class($obj);
			}
		}

		return $array;
	}


	public function as_links_array($keys = array())
	{
		$array = array();
		if(empty($keys) or empty($keys[0])) $keys[0] = $this->primary_key;
		if(empty($keys) or empty($keys[1])) $keys[1] = $this->primary_val;
				
		if ($results = $this->result->result_array())
		{
			ksort($keys);
			// Import class name

			$i = 0;
			foreach ($results as $obj)
			{
				foreach($keys as $key):
					$array[$i][] = $obj->$key;
				endforeach;
				$i++;
			}
		}

		return $array;
	}

/**
 * get list of IDs in current list, of list of specified field
 */
	
	public function getValues($field = NULL){
		if(!$this->count()) return array();
		
		if($field == NULL) return array_keys($this->select_list());
		
		$array = array();
		
		foreach($this as $item):
			if(!isset($array[$item->$field]))
			$array[$item->$field] = $item->$field;
		endforeach;
		
		return $array;
	}
	
	public function getIDs(){
		return $this->getValues($this->primary_key);
	}
	
}

/* ?> */