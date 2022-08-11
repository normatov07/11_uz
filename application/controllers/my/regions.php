<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Profile controller
 */		
				
class Regions_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
		$this->view = new View('my/regions_view');
//		$this->addJs('regions.js');	
		$this->title = "Настройки региона";
	}

	public function index()
	{
	
//*				
		try{

// */				
			if (!empty($_POST)){
			
				
				if(empty($_POST['region']) or !count($_POST['region']) or $_POST['region'][0] == '_all_'):
					
					if($this->isLoggedIn()):
						foreach($this->user->regions as $item):
							$this->user->remove($item);
						endforeach;
						$this->user->save();
					endif;
					
					AppLib::setUserRegions(array());
					
				elseif(count($_POST['region'])):					
					
					$regions = ORM::factory('region')->in('id',$_POST['region'])->find_all();
					
					AppLib::setUserRegions($regions->select_list());
					
					if($this->isLoggedIn()):
					
						foreach($this->user->regions as $item):
							if(!in_array($item->id, $_POST['region'])) $this->user->remove($item);
						endforeach;						
						
						foreach($regions as $item):
							$this->user->add($item);
						endforeach;
						
						$this->user->save();
						
					endif;
										
				endif;
				
				$this->messages->add('Настройки успешно сохранены');
				$this->redirect = '/';
				return; 
			}
			
		} catch (Kohana_Exception $e) {
			$this->handleException($e);
		}			

		$this->view->selected_regions = AppLib::getUserRegions(true);
		$this->view->regions = ORM::factory('region')->find_all();

	}


}
/* ?> */