<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bookmarks controller
 */
class Bookmarks_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		if(!$this->hasAccess('user')) return;

		$this->perpage = 20;
	}

	public function index($page = 0)
	{
		if(!$this->hasAccess('user')) return;


		if(!empty($_POST)):
			if(!empty($_POST['id']) and count($_POST['id'])):

				if(!empty($_POST['delete_selected'])):

					if(ORM::factory('bookmark')->delete($_POST['id'])):

						$this->data['act'] = 'delete_selected';
						$this->messages->add('Закладки удалены!');

					endif;

				endif;

			else:
				$this->errors->add('Не выбрано ни одной закладки!');
			endif;
		endif;


		if(!request::is_ajax()):

			$this->template->titleBlock = new View('my/b_title_view');
			$this->title = $this->template->titleBlock->title = "Мои закладки";
			$this->template->titleBlock->pageid = 'bookmarks';
			$this->view = new View('my/bookmarks_view');

			$this->addJs('jquery.jqModal.js');
			$this->addJs('offer.js');
			$this->addJs('edit_list.js');

			if($this->view->count_total = $bookmarksCount = ORM::factory('bookmark')->count_all_by_user($this->user->id)):

				$paginationConfig = array(
					'total_items'    => $this->view->count_total, // use db count query here of course
					'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
					'item_title'		=> array('закладка','закладки', 'закладок'),
				);

				$pagination = new Pagination($paginationConfig);

				$this->view->bookmarkList = ORM::factory('bookmark')->find_all_by_user($this->user->id, $this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
				$this->view->offerIDs = $this->view->bookmarkList->getValues('offer_id');

				if(count($this->view->offerIDs)) $this->view->offerList = ORM::factory('offer')->in('id', $this->view->offerIDs)->find_all();

				$this->view->pagination = $pagination;

			endif;
		endif;


	}

/**
 * Удаление пользователем
 */

	public function delete($bookmark_id = NULL){

		if(!$this->hasAccess('user')) return;

		if(empty($bookmark_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;

		$this->bookmark = new Bookmark_Model($bookmark_id);

		if($this->bookmark->id == 0 or !$this->bookmark->is_viewed_by_owner) { $this->redirect = '/'; return; }

//*
		try{
//*/
			if (! empty($_POST)):

				if(!empty($_POST['cancel'])):
					$this->redirect = $this->bookmark->url;
					return;
				else:

					$_POST = new Validation($_POST);

					$_POST->pre_filter('trim',true);

					if ($_POST->validate()):
						$this->data['id'] = $this->bookmark->id;
						if($this->bookmark->delete()):
							$this->redirect = $this->bookmark->url_delete_success;
							return;
						endif;
					else: // is valid
						$this->errors->add($_POST->list_errors());
						$this->obj = $_POST;
					endif; //is valid

				endif;

			endif;// POST
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/

		$this->view = new View('my/bookmark_delete_view');

		$this->title = 'Удаление закладки';
		$this->template->titleInView = true;

		$this->view->bookmark = $this->bookmark;
		if(empty($_POST)) $this->returnViewInAjax = true;

	} // delete

	public function delete_success($offer_id = NULL){

		$this->title = 'Закладка удалена!';

	}


}
/* ?> */