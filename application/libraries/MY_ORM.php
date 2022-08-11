<?php defined('SYSPATH') or die('No direct script access.');

class ORM extends ORM_Core {
	
	protected $has_priority;
	protected $primary_val = 'title';

	public $is_not_post = true;

    protected $reload_on_wakeup   = FALSE;

	protected $delete_belongings_exception;

    public function cache_columns($data, $level = 1) {

        switch($level) {
            case 1:
                Cache::instance()->set(
                    $this->table_name . '_structure',
                    $data,
                    null, // tags
                    36000 + mt_rand(0, 3600)
                );
                // break omitted explicitly
            case 2:
                self::$column_cache[$this->object_name] = $data;
                // break omitted explicitly
            case 3:
                $this->table_columns = $data;
                // break omitted explicitly
        }
    }

    private function fetch_columns() {
        return $this->db->list_fields($this->table_name, TRUE);
    }

    private function clear_columns_cache() {
        Cache::instance()->delete($this->table_name . '_structure');
        self::$column_cache[$this->object_name] = $this->table_columns = array();
    }

    public function reload_columns($force = FALSE) {
        // reload cache with force
        if ($force) {
            $this->clear_columns_cache();
        }

        // object cache hit
        if (!empty($this->table_columns)) {
            return $this;
        }

        // static memory cache hit
        if (!empty(self::$column_cache[$this->object_name])) {
            $this->cache_columns(self::$column_cache[$this->object_name], 3);
            return $this;
        }

        $cached = Cache::instance()->get($this->table_name . '_structure');
        // driver cache hit
        if ($cached) {
            $this->cache_columns($cached, 2);
            return $this;
        }

        // cache miss
        $this->cache_columns($this->fetch_columns());
        return $this;
    }

    // REMOVING load_type function
    public function load_values(array $values)
    {
        if (array_key_exists($this->primary_key, $values))
        {
            // Replace the object and reset the object status
            $this->object = $this->changed = $this->related = array();

            // Set the loaded and saved object status based on the primary key
            $this->loaded = $this->saved = ($values[$this->primary_key] !== NULL);
        }

        // Related objects
        $related = array();

        foreach ($values as $column => $value)
        {
            if (strpos($column, ':') === FALSE)
            {
                $this->object[$column] = $value;
            }
            else
            {
                list ($prefix, $column) = explode(':', $column, 2);

                $related[$prefix][$column] = $value;
            }
        }

        if ( ! empty($related))
        {
            foreach ($related as $object => $values)
            {
                // Load the related objects with the values in the result
                $this->related[$object] = $this->related_object($object)->load_values($values);
            }
        }

        return $this;
    }

    public function __set($column, $value)
    {
        if (isset($this->ignored_columns[$column]))
        {
            return NULL;
        }
        elseif (isset($this->object[$column]) OR array_key_exists($column, $this->object))
        {
            if (isset($this->table_columns[$column]))
            {
                // Data has changed
                $this->changed[$column] = $column;

                // Object is no longer saved
                $this->saved = FALSE;
            }

            $this->object[$column] = $value;
        }
        elseif (in_array($column, $this->has_and_belongs_to_many) AND is_array($value))
        {
            // Load relations
            $model = ORM::factory(inflector::singular($column));

            if ( ! isset($this->object_relations[$column]))
            {
                // Load relations
                $this->has($model);
            }

            // Change the relationships
            $this->changed_relations[$column] = $value;

            if (isset($this->related[$column]))
            {
                // Force a reload of the relationships
                unset($this->related[$column]);
            }
        }
        else
        {
            throw new Kohana_Exception('core.invalid_property', $column, get_class($this));
        }
    }

    public function load_type($column, $value) {
        return $value;
    }
    // END OF REMOVING load_type function


    public function __construct($id=FALSE)
    {
        parent::__construct($id);
    }
	
	public function setValuesFromArray($data)
	{
		foreach ($data as $field=>$value)
		{
			if(array_key_exists($field, $this->table_columns))
			{
				$this->$field = $value;
			}
		}
	}
	
	public function save(){
	
		if(!empty($this->has_priority)) $this->setPriority();
		return parent::save();
		
	}	
	
	/**
	 * Deletes this object, or all belonging objects of this table.
	 *
	 * @param   bool  delete all rows in table
	 * @return  bool  FALSE if the object cannot be deleted
	 * @return  int   number of rows deleted
	 */	
	public function delete_belongings($exception = NULL)
	{

		if(empty($this->id)) return FALSE;
		
		if($exception == NULL and !empty($this->delete_belongings_exception) and is_array($this->delete_belongings_exception)):
			$exception = $this->delete_belongings_exception;
		endif;
		
			
		if ( ! empty($this->has_and_belongs_to_many)){

			foreach ($this->has_and_belongs_to_many as $object){
				if(($exception == NULL or (is_array($exception) and !in_array($object, $exception))) and $objs = @$this->$object){
					foreach($objs as $obj){
						$this->remove($obj);
					}
				}				
			}
		
		}		
			
		if ( ! empty($this->has_many)){

			foreach ($this->has_many as $object){
				if(!empty($exception) and is_array($exception) and in_array($object, $exception)):
					$this->db->set($this->foreign_key(), 0)->where($this->foreign_key(), $this->id)->update($object);
				elseif($objs = @$this->$object):
					foreach($objs as $key=>$obj){
						$obj->delete();						
					}
				endif;
			}
		
		}

		if ( ! empty($this->has_one)){

			foreach ($this->has_one as $object)
			{
				if(!empty($exception) and is_array($exception) and in_array($object, $exception)):
					$this->db->set($this->foreign_key(), 0)->where($this->foreign_key(), $this->id)->update(inflector::plural($object));
				elseif($obj = @$this->$object):
					$obj->delete();
				endif;
			}
			
		}
		
		return true;
	}
	
	public function delete($id = NULL, $except = NULL)
	{
		// Can't delete something that does not exist
		
		if(!empty($id)):
			if(is_array($id)):
				
				$list = $this->in($this->primary_key, $id)->find_all();
				foreach($list as $item):
					$item->delete();
				endforeach;
				return true;
				
			else:
				return $this->where($this->primary_key, $id)->find()->delete();
			endif;
			
		elseif (empty($this->id)):
			return FALSE;		
		endif;
		
		if(!empty($this->has_priority)) 
			$this->setPriority('delete');
	

		if($this->delete_belongings($except))
			return parent::delete();
		
		return false;
	}


	public function find_all($limit = NULL, $offset = 0)
	{

		if ( ! isset($this->db_applied['orderby']))
		{

			if($columnname = $this->getPriorityColumn() and array_key_exists($columnname, $this->sorting)):
				
				if(!empty($this->db_applied['select'])):
					$this->db->select('*');
				endif;
				
				$this->sorting = array_merge(array('('.$this->table_name.'.'.$columnname.' IS NULL)' => 'asc'), $this->sorting);
				
			endif;
			
		}
	
		return parent::find_all($limit, $offset);
		
	}



/**
 * STATUSES
 */
	
	public function isNew(){
		return !$this->loaded;
	}
	
	public function isDirty(){
		return !$this->saved;
		
	}
		
	public function isChanged($key = NULL){
		if(!empty($key)) return in_array($key, $this->changed);
		return count($this->changed);		
	}
	
	public function isEmpty(){
		return (!$this->loaded or $this->id == 0);
		
	}



/**
 * UTILITIES
 */

	public function enable($columnname = 'status'){

		if(!$this->loaded or !array_key_exists($columnname, $this->table_columns)) return false;
			
		if($this->$columnname === 'disabled'):
			$this->$columnname = 'enabled';
			$this->save();
		elseif($this->$columnname === 0):
			$this->$columnname = 1;
			$this->save();		
		endif;

		return true;
	}

/**
 * Object data fetch function
 *
 * @param   string  table name
 * @return  array  data
 */
	public function data($includeonly = array()){
		if(!is_array($includeonly) and !empty($this->includeindataonly) and count($this->includeindataonly)) $includeonly = &$this->includeindataonly;
		
		if(count($includeonly)):
			$rawdata = parent::as_array();
			
			foreach($includeonly as $key):
				$data[$key] = $rawdata[$key];
			endforeach;
			
			return $data;	
		else:
			return parent::as_array();
		endif;
	}	


/**
 * PRIORITY RELATED FUNCTIONS
 */

	private function getPriorityColumn(){
		
		if(empty($this->has_priority)) return false;
		
		if(is_string($this->has_priority)):
			$columnname = $this->has_priority;
		else:
			$columnname = 'priority';
		endif;
		
		if(!array_key_exists($columnname, $this->table_columns)) return false;
		
		return $columnname;
	}
	

	public function setPriority($act = 'save'){

	
		if(!$columnname = $this->getPriorityColumn()) return;
		

		if($this->isNew()):
		
			if($this->$columnname !== ''):
				$this->set($columnname, $columnname . ' + 1 ', TRUE)->where($columnname . ' >= ', $this->$columnname)->db->update($this->table_name);
			endif;
			
		else:
			
			if(array_key_exists($columnname, $this->changed)):
				$old_priority = $this->db->select($columnname)->from($this->table_name)->where($this->primary_key, $this->id)->get()->current()->$columnname;
			else:
				$old_priority = $this->$columnname;
			endif;
			
			if($act == 'delete' or ($this->$columnname === '' and !empty($old_priority))):

				$this->db->set($columnname, $columnname . ' - 1', TRUE)->where($columnname . ' > ', $old_priority)->update($this->table_name);
					
			elseif($old_priority == $this->$columnname):

				return;
			
			elseif($old_priority > $this->$columnname):

				$this->db->set($columnname, $columnname . '+1', TRUE)->where(array($columnname . ' >= ' => $this->$columnname, $columnname . ' < ' => $old_priority))->update($this->table_name);

			else:
			
				$this->db->set($columnname, $columnname . '-1', TRUE)->where(array($columnname . ' > ' => $old_priority , $columnname. ' <= ' => $this->$columnname))->update($this->table_name);
			
			endif;					
			
		endif;

		
	}


/**
 * STATUS CHANGE
 */


	public function setStatusChange($status = NULL, $prev_status = NULL, $by = NULL, $reason = NULL){
	
		$status_change = $this->getStatusChange($status, $by);
	
		if(!empty($prev_status) and $prev_status != $status_change->prev_status):
			$status_change->prev_status = $prev_status;
			$changed = true;
		endif;
		
		if(!empty($reason) and $reason != $status_change->reason):
			$status_change->reason = $reason;
			$changed = true;
		endif;
		
		if(!empty($changed) or !$status_change->id):
			$status_change->by_id = $by;
			$status_change->save();
		endif;
		
		return $status_change;
		
	}
	
	public function getStatusChange($status = NULL, $by = NULL){
		
		$status_change = ORM::factory('status_change')->find(array('status' => (!empty($status)?$status:$this->status), $this->foreign_key() => $this->id));
	
		if(!$status_change->id) {
			$status_change = new Status_change_Model;
			$status_change->{$this->foreign_key()} = $this->id;
			$status_change->status = $status;
			$status_change->prev_status = 'enabled';
			$status_change->by_id = $by;
		}
		
		return $status_change;
	}

	
}

/* ?> */
