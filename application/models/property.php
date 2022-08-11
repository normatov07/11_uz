<?php defined('SYSPATH') or die('No direct script access.');

class Property_Model extends ORM {

	protected $belongs_to = array('category', 'list');
	protected $has_many = array('datas');	
	
	protected $sorting = array('priority' => 'asc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}
/*
	public function __get($column)
	{
		if ($column === 'list' and !isset($this->related[$column]) and !$this->primary_key_value and $model = $this->related_object($column))
		{
			return $this->related[$column] = $model;
		}
		
		return parent::__get($column);
	
	}
*/
	public function formField($value = NULL, $mode = NULL){
	
		if($this->list_id != 0) $this->datatype = 'list';
					
		$data = array();
		$extras = array();
		
		if($mode == 'filter'){
		
			$data['name'] = 'filter['.$this->id.']';
			$data['id'] = 'f'.$this->id;
			$data['title'] = $this->title;
			if($this->datatype == 'list' and $this->list->listtype == 'select') $data['class'] = 'nodis'; 
						
		}else{
		
			$data['name'] = 'property['.$this->id.']';		
			$data['id'] = 'property'.$this->id;			
		
			if(!empty($this->maxlength)) $data['maxlength'] = $this->maxlength;
			if(!empty($this->required)) $data['class'] = 'req';
			$data['title'] = $this->title;
			
		};		
		
		switch($this->datatype):
			case 'boolean':
			
				return form::yesno($data, $value);
				
			break;
			case 'list':
			
				$options = array(); $default = '';
				
				if($mode == 'filter'):
					$options = array('' => 'не выбрано');
				elseif($this->list->listtype == 'select' and $this->list->default_empty):
					$data['class'] = 'nodis';
					if(!empty($this->required)) $data['class'] .= ' req';
					$options = array('' => $this->list->default_empty != 1? $this->list->default_empty: 'Выберите') + $options;
				endif;
						
				foreach($this->list->list_items as $option):
					$options[$option->valuedata] = $option->title;
					if(@$option->isdefault != 0) $default = $option->valuedata;
				endforeach;
				
				$output = '';
				
				if($this->list->has_other and $mode != 'filter'):
				
					$options['other'] = $this->list->has_other != 1?$this->list->has_other : 'другое';
					
					$othervalue = '';
					
					if(!empty($value)):
					
						if(!is_array($value) and !in_array($value, array_keys($options))):
							$othervalue = $value;
							$value = 'other';
						elseif(is_array($value) and count($othervalues = array_values(array_diff($value, array_keys($options))))):
							$othervalue = $othervalues[0];
							$value = $value[0];
						endif;
						
					endif;	
					
					$otherdata = $data;
					
					$otherdata['name'] = $data['name'].'[other]';
					$otherdata['id'] = $data['id'].'_other';
					$otherdata['class'] = 'other_input';
					
					$output = ' '.form::input($otherdata, $othervalue);
					
					if($this->list->listtype != 'checkbox') $data['name'] = $data['name'].'[]';
					
				endif;
				
				switch($this->list->listtype):
					case 'select':		
						$output = form::dropdown($data, $options, $value != NULL?$value:$default) . $output . (!empty($this->units)? ' ' . $this->units:'');
					break;
					case 'checkbox':
						$data['delimiter'] = "<br />\n";
						$data['splittocols'] = 2;
						$data['minincol'] = 6;
						$output = form::checkbox_list($options, $data, $value) . $output;
					break;
					case 'radio':
						$data['delimiter'] = "<br />\n";
						$output = form::radio_list($options, $data, $value != NULL?$value:$default) . $output;
					break;
				endswitch;
								
				return $output;
				
			break;
			default:
				
				return form::input($data, $value) . (!empty($this->units)? ' ' . $this->units:'');
				
			break;
		endswitch;
	}

}
/* ?> */