<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Offers controller
 */
class Offers_Controller extends AdmController {

	public function __construct()
	{
		parent::__construct();

		$this->perpage = 20;
		$this->modelName = 'offer';

	}

	public function index($page = 0)
	{
		if(!$this->hasAccess('moderator')) return;

		if(!empty($_POST)):

				$this->data['mode'] = 'moder';

				if(!empty($_POST['id']) and count($_POST['id'])):

					if(!empty($_POST['delete_selected'])):

						if(ORM::factory('offer')->where('status!=','banned')->setDeleted($_POST['id'])):

							$this->data['act'] = 'delete_selected';
							$this->messages->add('Объявления удалены!');

						endif;
					elseif(!empty($_POST['remove_selected'])):

						if($offerslist = ORM::factory('offer')->in('id',$_POST['id'])->find_all()):

							foreach($offerslist as $item):
								$item->delete();
							endforeach;

						endif;

						$this->data['act'] = 'remove_selected';
						$this->messages->add('Объявления удалены совсем!');


					elseif(!empty($_POST['recover_selected'])):

						if(ORM::factory('offer')->where('status!=','banned')->unDelete($_POST['id'])):

							$this->data['act'] = 'recover_selected';
							$this->messages->add('Объявления восстановлены!');

						endif;

					elseif(!empty($_POST['disable_selected'])):

						if(ORM::factory('offer')->where('status!=','banned')->setDisabled($_POST['id'])):

							$this->data['act'] = 'disable_selected';
							$this->messages->add('Объявления отключены');

						endif;

					elseif(!empty($_POST['enable_selected'])):


						if($dates = ORM::factory('offer')->setEnabled($_POST['id'])):
							$this->data['dates'] = $dates;
							$this->data['act'] = 'enable_selected';

							$this->messages->add('Объявления активированы!');

						endif;

					elseif(!empty($_POST['remove_selected'])):
						if(ORM::factory('offer')->delete($_POST['id'])):

							$this->data['act'] = 'remove_selected';
							$this->messages->add('Объявления полностью удалены!');

						endif;

					elseif(!empty($_POST['premium_selected'])):


					elseif(!empty($_POST['mark_selected'])):


					elseif(!empty($_POST['position_selected'])):

					endif;

				else:
					$this->errors->add('Не выбрано ни одного объявления!');
				endif;


		endif;


		if(!request::is_ajax()):

			$this->title = "Модерация объявлений";

			$this->view = new View('adm/offers_view');
			$this->addJs('jquery.jqModal.js');
			$this->addJs('offer.js');
			$this->addJs('edit_list.js');
			$this->addJs('adm_offers.js');

			$this->view->types = array(''=>'любой') + ORM::factory('type')->find_all()->select_list();

			$categories = ORM::factory('category')->where('status','enabled')->find_all()->as_id_array();

			$maincategories = $categoryIDs = array();

			foreach($categories as $category):
				if($category->level == 1):
					$maincategories[$category->id] = $category->title;
				endif;
				if(!empty($_REQUEST['category_id'])):
					if($_REQUEST['category_id'] == $category->id):
						$currentCategoryLeftKey = $category->left_key;
						$currentCategoryRightKey = $category->right_key;
						$categoryIDs[] = $category->id;
					elseif(!empty($currentCategoryLeftKey) and !empty($currentCategoryRightKey)
						and $category->left_key > $currentCategoryLeftKey
						and $category->right_key < $currentCategoryRightKey
						):
						$categoryIDs[] = $category->id;
					endif;
				endif;
			endforeach;

			$this->view->categories = $categories;
			$this->view->maincategories = $maincategories;

			$offers = ORM::factory('offer');

            $this->view->q_str = '';
			if(!empty($_GET['q'])) :
                @$_REQUEST['q'] = substr(trim(@$_GET['q']), 0, 45);
                $this->view->q_str = $_REQUEST['q'];
            endif;

			$filters = @$_REQUEST;
			$filters['ignore_regions'] = true;

			if(count($categoryIDs)):
				$filters['category_id'] = $categoryIDs;
			endif;

            if (isset($_REQUEST['q']) && !empty($_REQUEST['q'])
                    && valid::phone($_REQUEST['q'])):

                $filters['phone'] = $_REQUEST['q'];
                unset($_REQUEST['q']);
                unset($filters['q']);
            endif;

			if (isset($_REQUEST['q']) && !empty($_REQUEST['q'])):
				try
				{
					$filters['period'] = @$filters['added_period'];
					unset($filters['added_period']);
					unset($filters['ignore_regions']);
					unset($filters['q']);
					$found_data = array();

					$search = new search;
					$found_data = $search->search($_REQUEST['q'], $filters, NULL, $this->perpage, $this->perpage*($page-1));

					$found_data['matches'] = isset($found_data['matches'])?$found_data['matches']:array();
					$found_data['total'] = isset($found_data['total'])?$found_data['total']:0;
					$found_ids = array_keys($found_data['matches']);
					$found_offers = array();
					if (!empty($found_ids)):

						$offers = ORM::factory('offer');

                        $offers_data = $offers->find_by_ids($found_ids);
						$this->view->offerList = $offers_data;
						$this->view->offersCount = $found_data['total'];

						$paginationConfig = array(
							'total_items'    => @$this->view->offersCount, // use db count query here of course
							'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
							'item_title'		=> array('объявление','объявления', 'объявлений'),
						);
						$pagination =  new Pagination($paginationConfig);
						$this->view->pagination = $pagination;

						$this->view->regions = ORM::factory('region')->find_all()->select_list();
						$offerIDs = $this->view->offerList->getIds();

						if(count($offerIDs)) $this->view->pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);

						$userIDs = $this->view->offerList->getValues('user_id');

						if(count($userIDs)) $this->view->users = ORM::factory('user')->in('id', $userIDs)->find_all()->as_id_array();
					endif;
				}
				catch(Exception $e)
				{
					@Lib::log($e->getMessage().':'.$e->getTraceAsString());
				}
			else:
				if($this->view->offersCount = $offers->setFilters(@$filters)->count_all()):

					$paginationConfig = array(
						'total_items'    => $this->view->offersCount, // use db count query here of course
						'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
						'item_title'		=> array('объявление','объявления', 'объявлений'),
					);

					$pagination = new Pagination($paginationConfig);
					$this->view->pagination = $pagination;

					$this->view->offerList = $offers->setFilters(@$filters)->orderby('added', 'desc')->find_all($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);

					$this->view->regions = ORM::factory('region')->find_all()->select_list();
					$offerIDs = $this->view->offerList->getIds();

					if(count($offerIDs)) $this->view->pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);

					$userIDs = $this->view->offerList->getValues('user_id');

					if(count($userIDs)) $this->view->users = ORM::factory('user')->in('id', $userIDs)->find_all()->as_id_array();
				endif;
			endif;
		endif;
	}


}
/* ?> */