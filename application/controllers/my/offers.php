<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Offers controller
 */
class Offers_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->perpage = 20;


	}

	public function index($mode = 'all', $page = 0)
	{
		if(!$this->hasAccess('user')) return;

		if(!in_array($mode, array('all','banned','enabled','disabled','deleted'))) $mode = 'all';

		if(!empty($_POST)):

			$this->data['mode'] = $mode;

			if($this->hasAccess('enabled')):

				if(!empty($_POST['id']) and count($_POST['id'])):

					if(!empty($_POST['delete_selected'])):

						if(ORM::factory('offer')->where($this->user->foreign_key(), $this->user->id)->where('status!=','banned')->setDeleted($_POST['id'])):

							$this->data['act'] = 'delete_selected';
							$this->messages->add('Объявления удалены!');

						endif;

					elseif(!empty($_POST['recover_selected'])):

						if(ORM::factory('offer')->where($this->user->foreign_key(), $this->user->id)->where('status!=','banned')->unDelete($_POST['id'])):

							$this->data['act'] = 'recover_selected';
							$this->messages->add('Объявления восстановлены!');

						endif;

					elseif(!empty($_POST['disable_selected'])):

						if(ORM::factory('offer')->where($this->user->foreign_key(), $this->user->id)->where('status!=','banned')->setDisabled($_POST['id'])):

							$this->data['act'] = 'disable_selected';
							$this->messages->add('Объявления отключены');

						endif;

					elseif(!empty($_POST['enable_selected'])):

                        $offer = ORM::factory('offer')->where($this->user->foreign_key(), $this->user->id)->where('status!=','banned')->find();
                        $category_warning = $offer->category->check_offers_limit($this->user);						/*
                        if ($category_warning['warning_status']):
                            $this->errors->add('Физическое лицо может размещать не более 2-х объявлений в выбранном разделе.');
                        else:												endif;						*/
                            if($dates = $offer->setEnabled($_POST['id'])):

                                $this->data['dates'] = $dates;
                                $this->data['act'] = 'enable_selected';

                                $this->messages->add('Объявления активированы!');

                            endif;
                       

					elseif(!empty($_POST['remove_selected'])):
						if(ORM::factory('offer')->where($this->user->foreign_key(), $this->user->id)->delete($_POST['id'])):

							$this->data['act'] = 'remove_selected';
							$this->messages->add('Объявления полностью удалены!');

						endif;

					elseif(!empty($_POST['premium_selected'])):
						$this->data['act'] = 'premium_selected';
						$this->redirect = '/my/payment/offer/premium/?id[]='.join('&id[]=',$_POST['id']);
						return;

					elseif(!empty($_POST['mark_selected'])):
						$this->data['act'] = 'mark_selected';
						$this->redirect = '/my/payment/offer/mark/?id[]='.join('&id[]=',$_POST['id']);
						return;

					elseif(!empty($_POST['position_selected'])):
						$this->data['act'] = 'position_selected';
						$this->redirect = '/my/payment/offer/position/?id[]='.join('&id[]=',$_POST['id']);
						return;
					endif;

				else:
					$this->errors->add('Не выбрано ни одного объявления!');
				endif;


			endif;

		endif;


		if(!request::is_ajax()):

			$this->template->titleBlock = new View('my/b_title_view');
			$this->title = $this->template->titleBlock->title = "Мои объявления";
			$this->template->titleBlock->pageid = 'offers';

			$this->view = new View('my/offers_view');
			$this->addJs('jquery.jqModal.js');
			$this->addJs('offer.js');
			$this->addJs('edit_list.js');

			$this->view->mode = $mode;

/**
 * Проставляем просроченные объявления
 */			ORM::factory('offer')->setExpiredOffers($this->user->id);

			$where = array(
				$this->user->foreign_key() => $this->user->id
			);

			if(!$this->user->is_agent):
				$where['has_not_user'] = 0;
			endif;

			$this->view->count_total = ORM::factory('offer')->where($where)->where('status!=','deleted')->count_all();
			$this->view->count_enabled = ORM::factory('offer')->where($where)->where('status','enabled')->count_all();
			$this->view->count_disabled = ORM::factory('offer')->where($where)->notin('status',array('enabled','deleted'))->count_all();
			$this->view->count_banned = ORM::factory('offer')->where($where)->where('status','banned')->count_all();
			$this->view->count_deleted = ORM::factory('offer')->where($where)->where('status','deleted')->count_all();

			$offers = ORM::factory('offer')->where($where);

			switch($mode):
				case 'deleted':
					$this->view->notfound = 'Нет удалённых объявлений.';
					$offersCount = $this->view->count_deleted;
					$offers->where('status',$mode);
				break;
				case 'banned':
					$this->view->notfound = 'Нет заблокированных объявлений.';
					$objsCount = $this->view->count_banned;
				break;
				case 'enabled':
					if(empty($this->view->notfound)) $this->view->notfound = 'Нет активных объявлений.';
					$offers->where('status',$mode);
					$offersCount = $this->view->count_enabled;
				break;
				case 'disabled':
					$this->view->notfound = 'Нет неактивных объявлений.';
					$offers->notin('status',array('enabled','deleted'));
					$offersCount = $this->view->count_disabled;
				break;
				case 'all':
				default:
					$offers->where('status !=','deleted');
					$offersCount = $this->view->count_total;
				break;
			endswitch;

			if(!empty($offersCount)):

				$paginationConfig = array(
					'total_items'    => $offersCount, // use db count query here of course
					'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
					'item_title'		=> array('объявление','объявления', 'объявлений'),
				);

				$pagination = new Pagination($paginationConfig);

				$this->view->offerList = $offers->find_all($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);

				$this->view->pagination = $pagination;

			endif;

		endif;
	}


}
/* ?> */